<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Queue Jobs</h4>
        <p class="text-muted small mb-0">
            <span class="fw-semibold text-primary">{{ $pendingCount }}</span> pending &nbsp;·&nbsp;
            <span class="fw-semibold text-warning">{{ $reservedCount }}</span> reserved &nbsp;·&nbsp;
            <span class="fw-semibold text-danger">{{ $failedCount }}</span> failed
        </p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filters --}}
<form method="GET" class="row g-2 mb-4 align-items-end">
    <div class="col-sm-4 col-md-3">
        <input type="text" name="queue" class="form-control form-control-sm"
               placeholder="Queue name…" value="{{ request('queue') }}">
    </div>
    <div class="col-sm-auto">
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
        <a href="{{ route('admin.jobs') }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
    </div>
</form>

<h6 class="fw-semibold mb-2">Pending &amp; Reserved</h6>
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Queue</th>
                    <th>Job</th>
                    <th>Attempts</th>
                    <th>Status</th>
                    <th>Queued At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingJobs as $job)
                    <tr>
                        <td class="text-muted small">{{ $job->id }}</td>
                        <td class="small">{{ $job->queue }}</td>
                        <td class="fw-semibold small">{{ $job->job_name }}</td>
                        <td class="small">{{ $job->attempts }}</td>
                        <td>
                            <span class="badge {{ $job->status === 'reserved' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ \Illuminate\Support\Carbon::createFromTimestamp($job->created_at)->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No pending or reserved jobs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($pendingJobs->hasPages())
        <div class="card-footer">{{ $pendingJobs->links() }}</div>
    @endif
</div>

<h6 class="fw-semibold mb-2">Failed</h6>
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Queue</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Failed At</th>
                    <th>Exception</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($failedJobs as $job)
                    <tr>
                        <td class="text-muted small">{{ $job->id }}</td>
                        <td class="small">{{ $job->queue }}</td>
                        <td class="fw-semibold small">{{ $job->job_name }}</td>
                        <td><span class="badge bg-danger">Failed</span></td>
                        <td class="small text-muted">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('d M Y H:i') }}</td>
                        <td class="small text-muted text-truncate d-inline-block" style="max-width:320px;" title="{{ $job->exception }}">
                            {{ \Illuminate\Support\Str::limit($job->exception, 80) }}
                        </td>
                        <td class="text-end text-nowrap">
                            <form action="{{ route('admin.jobs.retry', $job->uuid) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">Retry</button>
                            </form>
                            <form action="{{ route('admin.jobs.delete', $job->uuid) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this failed job permanently?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No failed jobs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($failedJobs->hasPages())
        <div class="card-footer">{{ $failedJobs->links() }}</div>
    @endif
</div>

</x-layouts.app>
