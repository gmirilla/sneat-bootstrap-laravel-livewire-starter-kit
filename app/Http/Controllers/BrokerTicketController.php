<?php

namespace App\Http\Controllers;

use App\Models\BrokerTicket;
use App\Models\BrokerTicketAttachment;
use App\Models\BrokerTicketMessage;
use App\Notifications\BrokerTicketNewActivity;
use App\Services\ProxyClient;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class BrokerTicketController extends Controller
{
    public function __construct(private readonly ProxyClient $proxy) {}

    public function index()
    {
        $tickets = BrokerTicket::where('user_id', Auth::id())
            ->with('latestMessage')
            ->latest()
            ->paginate(20);

        return view('broker.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $user     = Auth::user();
        $policies = Cache::get("elite_policies_{$user->broker_id}", []);

        if (empty($policies) && $this->proxy->isConfigured()) {
            $raw      = $this->proxy->call('GET', $this->proxy->getBaseUrl() . '/api/elite/broker/policies?broker_id=' . $user->broker_id);
            $json     = $raw ? json_decode($raw, true) : null;
            $policies = ($json['status'] ?? '') === 'success' ? ($json['data'] ?? []) : [];
            if (!empty($policies)) {
                Cache::put("elite_policies_{$user->broker_id}", $policies, 900);
            }
        }

        return view('broker.tickets.create', compact('policies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'policy_no'     => 'required|string|max:50',
            'subject'       => 'required|string|max:200',
            'priority'      => 'required|in:normal,high,urgent',
            'body'          => 'required|string|min:10|max:5000',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user   = Auth::user();
        $ticket = BrokerTicket::create([
            'user_id'   => $user->id,
            'broker_id' => $user->broker_id,
            'policy_no' => $request->policy_no,
            'subject'   => $request->subject,
            'priority'  => $request->priority,
            'status'    => 'open',
        ]);

        $message = BrokerTicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'body'        => $request->body,
            'is_internal' => false,
        ]);

        $this->storeAttachments($request, $message);

        Notification::route('mail', config('variables.TECHNICAL_EMAIL'))
            ->notify(new BrokerTicketNewActivity($ticket, $message, 'new_ticket'));

        return redirect()->route('broker.tickets.show', $ticket)
            ->with('success', 'Ticket #' . $ticket->id . ' opened successfully.');
    }

    public function show(BrokerTicket $ticket)
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $ticket->load(['messages.user', 'messages.attachments']);

        // Mark all database notifications for this ticket as read
        Auth::user()
            ->unreadNotifications()
            ->where('data->ticket_id', $ticket->id)
            ->update(['read_at' => now()]);

        return view('broker.tickets.show', compact('ticket'));
    }

    public function addMessage(Request $request, BrokerTicket $ticket)
    {
        abort_if($ticket->user_id !== Auth::id(), 403);
        abort_if(in_array($ticket->status, ['resolved', 'closed']), 422, 'This ticket is closed.');

        $request->validate([
            'body'          => 'required|string|min:5|max:5000',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $message = BrokerTicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'body'        => $request->body,
            'is_internal' => false,
        ]);

        $this->storeAttachments($request, $message);

        if ($ticket->status === 'awaiting_broker') {
            $ticket->update(['status' => 'in_progress']);
        }

        Notification::route('mail', config('variables.TECHNICAL_EMAIL'))
            ->notify(new BrokerTicketNewActivity($ticket->fresh(), $message, 'broker_reply'));

        return redirect()->route('broker.tickets.show', $ticket)
            ->with('success', 'Reply sent.');
    }

    public function downloadAttachment(BrokerTicketAttachment $attachment)
    {
        $ticket = $attachment->message->ticket;
        abort_if($ticket->user_id !== Auth::id() && !in_array(Auth::user()->role, ['admin', 'superadmin']), 403);

        return response()->download(
            Storage::disk('local')->path($attachment->path),
            $attachment->original_name
        );
    }

    private function storeAttachments(Request $request, BrokerTicketMessage $message): void
    {
        if (!$request->hasFile('attachments')) return;

        foreach ($request->file('attachments') as $file) {
            $path = $file->store("broker-tickets/{$message->ticket_id}/{$message->id}", 'local');
            BrokerTicketAttachment::create([
                'message_id'    => $message->id,
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);
        }
    }
}
