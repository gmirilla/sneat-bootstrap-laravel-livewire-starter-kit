<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">My Tickets</h4>
        <p class="text-muted small mb-0">All support tickets you have raised</p>
    </div>
    <a href="{{ route('broker.tickets.create') }}" class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
        <i class="bx bx-plus me-1"></i>Open Ticket
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
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
                        <tr onclick="window.location='{{ route('broker.tickets.show', $t) }}'" style="cursor:pointer;">
                            <td class="text-muted small">{{ $t->id }}</td>
                            <td class="fw-semibold small">{{ $t->policy_no }}</td>
                            <td>{{ $t->subject }}</td>
                            <td><span class="badge {{ $t->priorityColour() }}">{{ ucfirst($t->priority) }}</span></td>
                            <td><span class="badge {{ $t->statusColour() }}">{{ ucwords(str_replace('_', ' ', $t->status)) }}</span></td>
                            <td class="small text-muted">{{ $t->created_at->format('d M Y') }}</td>
                            <td class="small text-muted">{{ $t->latestMessage?->created_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No tickets yet.
                                <a href="{{ route('broker.tickets.create') }}">Open your first ticket</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($tickets->hasPages())
        <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
</div>

</x-layouts.app>
