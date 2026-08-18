<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminJobController extends Controller
{
    private function authorize(): void
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'superadmin']), 403);
    }

    public function index(Request $request)
    {
        $this->authorize();

        $pendingQuery = DB::table('jobs')->orderByDesc('created_at');
        $failedQuery  = DB::table('failed_jobs')->orderByDesc('failed_at');

        if ($request->filled('queue')) {
            $pendingQuery->where('queue', $request->queue);
            $failedQuery->where('queue', $request->queue);
        }

        $pendingJobs = $pendingQuery->paginate(15, ['*'], 'pending_page')->withQueryString();
        $failedJobs  = $failedQuery->paginate(15, ['*'], 'failed_page')->withQueryString();

        $pendingJobs->getCollection()->transform(function ($job) {
            $job->job_name = $this->jobDisplayName($job->payload);
            $job->status   = $job->reserved_at ? 'reserved' : 'pending';

            return $job;
        });

        $failedJobs->getCollection()->transform(function ($job) {
            $job->job_name = $this->jobDisplayName($job->payload);

            return $job;
        });

        $pendingCount = DB::table('jobs')->count();
        $reservedCount = DB::table('jobs')->whereNotNull('reserved_at')->count();
        $failedCount  = DB::table('failed_jobs')->count();

        return view('admin.jobs.index', compact(
            'pendingJobs', 'failedJobs', 'pendingCount', 'reservedCount', 'failedCount'
        ));
    }

    private function jobDisplayName(string $payload): string
    {
        $decoded = json_decode($payload, true);

        return $decoded['displayName'] ?? 'Unknown';
    }

    public function retry(string $uuid)
    {
        $this->authorize();

        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('success', "Job {$uuid} queued for retry.");
    }

    public function destroy(string $uuid)
    {
        $this->authorize();

        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);

        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('success', "Failed job {$uuid} deleted.");
    }
}
