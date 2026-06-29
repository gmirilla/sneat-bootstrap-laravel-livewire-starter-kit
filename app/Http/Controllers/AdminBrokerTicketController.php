<?php

namespace App\Http\Controllers;

use App\Models\BrokerTicket;
use App\Models\BrokerTicketAttachment;
use App\Models\BrokerTicketMessage;
use App\Notifications\BrokerTicketReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminBrokerTicketController extends Controller
{
    private function authorize(): void
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'superadmin']), 403);
    }

    public function index(Request $request)
    {
        $this->authorize();

        $query = BrokerTicket::with(['user', 'latestMessage'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('policy_no', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $tickets    = $query->paginate(20)->withQueryString();
        $openCount  = BrokerTicket::where('status', 'open')->count();
        $urgentCount = BrokerTicket::where('priority', 'urgent')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        return view('admin.broker-tickets.index', compact('tickets', 'openCount', 'urgentCount'));
    }

    public function show(BrokerTicket $ticket)
    {
        $this->authorize();

        $ticket->load(['user', 'messages.user', 'messages.attachments']);

        return view('admin.broker-tickets.show', compact('ticket'));
    }

    public function reply(Request $request, BrokerTicket $ticket)
    {
        $this->authorize();

        $request->validate([
            'body'          => 'required|string|min:5|max:5000',
            'is_internal'   => 'nullable|boolean',
            'status'        => 'required|in:open,in_progress,awaiting_broker,resolved,closed',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $isInternal = $request->boolean('is_internal');

        $message = BrokerTicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'body'        => $request->body,
            'is_internal' => $isInternal,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("broker-tickets/{$ticket->id}/{$message->id}", 'local');
                BrokerTicketAttachment::create([
                    'message_id'    => $message->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }

        $ticket->update(['status' => $request->status]);

        // Notify broker only for non-internal replies
        if (!$isInternal) {
            $ticket->user->notify(new BrokerTicketReplied($ticket->fresh(), $message));
        }

        return redirect()->route('admin.broker-tickets.show', $ticket)
            ->with('success', $isInternal ? 'Internal note added.' : 'Reply sent and broker notified.');
    }

    public function updateStatus(Request $request, BrokerTicket $ticket)
    {
        $this->authorize();
        $request->validate(['status' => 'required|in:open,in_progress,awaiting_broker,resolved,closed']);

        $ticket->update(['status' => $request->status]);

        return back()->with('success', "Ticket #{$ticket->id} marked as {$request->status}.");
    }

    public function downloadAttachment(BrokerTicketAttachment $attachment)
    {
        $this->authorize();

        return response()->download(
            Storage::disk('local')->path($attachment->path),
            $attachment->original_name
        );
    }
}
