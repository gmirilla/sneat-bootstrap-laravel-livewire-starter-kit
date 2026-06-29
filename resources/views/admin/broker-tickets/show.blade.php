<x-layouts.app>

<div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('admin.broker-tickets') }}" class="text-muted small">
        <i class="bx bx-arrow-back me-1"></i>Broker Tickets
    </a>
</div>

<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">{{ $ticket->subject }}</h5>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge {{ $ticket->statusColour() }}">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
            <span class="badge {{ $ticket->priorityColour() }}">{{ ucfirst($ticket->priority) }}</span>
            <span class="text-muted small">Policy: <strong>{{ $ticket->policy_no }}</strong></span>
            <span class="text-muted small">Broker: <strong>{{ $ticket->user->name }}</strong> (#{{ $ticket->broker_id }})</span>
            <span class="text-muted small">Ticket #{{ $ticket->id }}</span>
        </div>
    </div>

    {{-- Quick status update --}}
    <form action="{{ route('admin.broker-tickets.status', $ticket) }}" method="POST" class="d-flex gap-2 align-items-center">
        @csrf
        <select name="status" class="form-select form-select-sm" style="width:auto;">
            @foreach (['open','in_progress','awaiting_broker','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $s)) }}
                </option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Update</button>
    </form>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $e)<div class="small">{{ $e }}</div>@endforeach
    </div>
@endif

{{-- ── Thread ── --}}
<div class="mb-4">
    @foreach ($ticket->messages as $msg)
        @php
            $fromBroker = ($msg->user_id === $ticket->user_id);
        @endphp

        @if ($msg->is_internal)
            {{-- Internal note – admin-only, yellow tint --}}
            <div class="d-flex justify-content-center mb-3">
                <div style="max-width:85%;width:100%;">
                    <div class="small text-center text-muted mb-1">
                        <i class="bx bx-lock me-1 text-warning"></i>
                        Internal note · {{ $msg->user->name }} · {{ $msg->created_at->format('d M Y, H:i') }}
                    </div>
                    <div class="p-3 rounded-3 border border-warning text-dark"
                         style="background:#fffbeb;">
                        <div style="white-space:pre-wrap;line-height:1.65;">{{ $msg->body }}</div>
                        @if ($msg->attachments->isNotEmpty())
                            <div class="mt-2 pt-2 border-top border-warning">
                                @foreach ($msg->attachments as $att)
                                    <div class="small">
                                        <a href="{{ route('admin.broker.attachment.download', $att) }}"
                                           class="text-decoration-none text-dark">
                                            <i class="bx bx-download me-1"></i>{{ $att->original_name }}
                                            <span class="text-muted">({{ number_format($att->size / 1024, 0) }} KB)</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @elseif ($fromBroker)
            {{-- Broker message – left --}}
            <div class="d-flex justify-content-start mb-3">
                <div style="max-width:75%;">
                    <div class="small text-muted mb-1">
                        {{ $msg->user->name }} (Broker) &nbsp;·&nbsp; {{ $msg->created_at->format('d M Y, H:i') }}
                    </div>
                    <div class="p-3 rounded-3 shadow-sm"
                         style="background:#f8f9fc;color:#111827;border:1px solid #e5e9f2;">
                        <div style="white-space:pre-wrap;line-height:1.65;">{{ $msg->body }}</div>
                        @if ($msg->attachments->isNotEmpty())
                            <div class="mt-2 pt-2 border-top">
                                @foreach ($msg->attachments as $att)
                                    <div class="small">
                                        <a href="{{ route('admin.broker.attachment.download', $att) }}"
                                           class="text-decoration-none text-primary">
                                            <i class="bx bx-download me-1"></i>{{ $att->original_name }}
                                            <span class="text-muted">({{ number_format($att->size / 1024, 0) }} KB)</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            {{-- Admin reply – right --}}
            <div class="d-flex justify-content-end mb-3">
                <div style="max-width:75%;">
                    <div class="small text-muted mb-1 text-end">
                        {{ $msg->user->name }} &nbsp;·&nbsp; {{ $msg->created_at->format('d M Y, H:i') }}
                    </div>
                    <div class="p-3 rounded-3 shadow-sm"
                         style="background:#161616;color:#B18752;border:1px solid #2d2d2d;">
                        <div style="white-space:pre-wrap;line-height:1.65;">{{ $msg->body }}</div>
                        @if ($msg->attachments->isNotEmpty())
                            <div class="mt-2 pt-2 border-top" style="border-color:#2d2d2d!important">
                                @foreach ($msg->attachments as $att)
                                    <div class="small">
                                        <a href="{{ route('admin.broker.attachment.download', $att) }}"
                                           class="text-decoration-none text-warning">
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
        @endif
    @endforeach
</div>

{{-- ── Reply / Internal note ── --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header fw-semibold" style="background:#f8f9fc;">
        <i class="bx bx-reply me-1"></i>Reply / Add Note
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.broker-tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <textarea name="body" rows="5" class="form-control @error('body') is-invalid @enderror"
                          placeholder="Write your reply or internal note…"
                          minlength="5" maxlength="5000" required></textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Set status after reply</label>
                    <select name="status" class="form-select form-select-sm">
                        @foreach (['open','in_progress','awaiting_broker','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Attachments</label>
                    <input type="file" name="attachments[]" class="form-control form-control-sm"
                           multiple accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Up to 5 · PDF, JPG, PNG · max 5 MB each</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_internal" value="0">
                    <input class="form-check-input" type="checkbox" name="is_internal" value="1"
                           id="internalSwitch" role="switch">
                    <label class="form-check-label small" for="internalSwitch">
                        <i class="bx bx-lock me-1"></i>Internal note <span class="text-muted">(hidden from broker)</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-sm ms-auto" style="background:#161616;color:#B18752;font-weight:700;">
                    <i class="bx bx-send me-1"></i><span id="submitLabel">Send Reply</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('internalSwitch')?.addEventListener('change', function () {
    document.getElementById('submitLabel').textContent = this.checked ? 'Save Note' : 'Send Reply';
});
</script>

</x-layouts.app>
