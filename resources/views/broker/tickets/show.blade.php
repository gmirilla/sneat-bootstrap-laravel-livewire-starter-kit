<x-layouts.app>

<div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('broker.tickets') }}" class="text-muted small">
        <i class="bx bx-arrow-back me-1"></i>My Tickets
    </a>
</div>

<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">{{ $ticket->subject }}</h5>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge {{ $ticket->statusColour() }}">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
            <span class="badge {{ $ticket->priorityColour() }}">{{ ucfirst($ticket->priority) }}</span>
            <span class="text-muted small">Policy: <strong>{{ $ticket->policy_no }}</strong></span>
            <span class="text-muted small">Ticket #{{ $ticket->id }}</span>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Thread ── --}}
<div class="mb-4">
    @foreach ($ticket->messages as $msg)
        @php $isBroker = $msg->user_id === $ticket->user_id; @endphp
        <div class="d-flex {{ $isBroker ? 'justify-content-end' : 'justify-content-start' }} mb-3">
            <div style="max-width:75%;">
                <div class="small text-muted mb-1 {{ $isBroker ? 'text-end' : '' }}">
                    {{ $msg->user->name }} &nbsp;·&nbsp; {{ $msg->created_at->format('d M Y, H:i') }}
                </div>
                <div class="p-3 rounded-3 shadow-sm"
                     style="background:{{ $isBroker ? '#161616' : '#f8f9fc' }};
                            color:{{ $isBroker ? '#B18752' : '#111827' }};
                            border:1px solid {{ $isBroker ? '#2d2d2d' : '#e5e9f2' }};">
                    <div style="white-space:pre-wrap;line-height:1.65;">{{ $msg->body }}</div>

                    @if ($msg->attachments->isNotEmpty())
                        <div class="mt-2 pt-2 border-top" style="border-color:{{ $isBroker ? '#2d2d2d' : '#e5e9f2' }}!important">
                            @foreach ($msg->attachments as $att)
                                <div class="small">
                                    <a href="{{ route('broker.attachment.download', $att) }}"
                                       class="text-decoration-none {{ $isBroker ? 'text-warning' : 'text-primary' }}">
                                        <i class="bx bx-download me-1"></i>{{ $att->original_name }}
                                        <span class="opacity-75">({{ number_format($att->size / 1024, 0) }} KB)</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ── Reply box ── --}}
@if (!in_array($ticket->status, ['resolved', 'closed']))
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header fw-semibold" style="background:#f8f9fc;">
            <i class="bx bx-reply me-1"></i>Reply
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    @foreach ($errors->all() as $e)<div class="small">{{ $e }}</div>@endforeach
                </div>
            @endif
            <form action="{{ route('broker.tickets.message', $ticket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <textarea name="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                              placeholder="Type your message…" minlength="5" maxlength="5000" required></textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <input type="file" name="attachments[]" class="form-control form-control-sm"
                           multiple accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Up to 5 files · PDF, JPG or PNG · Max 5 MB each</div>
                </div>
                <button type="submit" class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
                    <i class="bx bx-send me-1"></i>Send Reply
                </button>
            </form>
        </div>
    </div>
@else
    <div class="alert alert-secondary text-center">
        <i class="bx bx-lock me-1"></i>This ticket is <strong>{{ $ticket->status }}</strong> and no longer accepts replies.
        <a href="{{ route('broker.tickets.create', ['policy_no' => $ticket->policy_no]) }}" class="ms-2">Open a new ticket</a>
    </div>
@endif

</x-layouts.app>
