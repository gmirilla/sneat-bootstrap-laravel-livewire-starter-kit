<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Broker Support Tickets</h4>
        <p class="text-muted small mb-0">
            <span class="fw-semibold text-danger">{{ $urgentCount }}</span> urgent &nbsp;·&nbsp;
            <span class="fw-semibold text-primary">{{ $openCount }}</span> open
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
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Policy / subject / broker…" value="{{ request('search') }}">
    </div>
    <div class="col-sm-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">All statuses</option>
            @foreach (['open','in_progress','awaiting_broker','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $s)) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-auto">
        <select name="priority" class="form-select form-select-sm">
            <option value="">All priorities</option>
            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="high"   {{ request('priority') === 'high'   ? 'selected' : '' }}>High</option>
            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
        </select>
    </div>
    <div class="col-sm-auto">
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
        <a href="{{ route('admin.broker-tickets') }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
    </div>
</form>

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Broker</th>
                    <th>Policy</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Opened</th>
                    <th>Last Activity</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $t)
                    <tr onclick="window.location='{{ route('admin.broker-tickets.show', $t) }}'" style="cursor:pointer;">
                        <td class="text-muted small">{{ $t->id }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $t->user->name }}</div>
                            <div class="text-muted" style="font-size:.75rem;">Broker #{{ $t->broker_id }}</div>
                        </td>
                        <td class="fw-semibold small">{{ $t->policy_no }}</td>
                        <td>{{ $t->subject }}</td>
                        <td><span class="badge {{ $t->priorityColour() }}">{{ ucfirst($t->priority) }}</span></td>
                        <td><span class="badge {{ $t->statusColour() }}">{{ ucwords(str_replace('_', ' ', $t->status)) }}</span></td>
                        <td class="small text-muted">{{ $t->created_at->format('d M Y') }}</td>
                        <td class="small text-muted">{{ $t->latestMessage?->created_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">No tickets match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($tickets->hasPages())
        <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
</div>

</x-layouts.app>
