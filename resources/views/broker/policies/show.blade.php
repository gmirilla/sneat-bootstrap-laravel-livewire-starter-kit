<x-layouts.app>

<div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('broker.policies') }}" class="text-muted small">
        <i class="bx bx-arrow-back me-1"></i>My Policies
    </a>
</div>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">{{ $policy['policy_no'] }}</h4>
        <p class="text-muted small mb-0">{{ $policy['product_type'] }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('broker.claims.create', ['policy_no' => $policy['policy_no']]) }}"
           class="btn btn-sm btn-outline-danger">
            <i class="bx bx-bell-plus me-1"></i>Submit Claim
        </a>
        <a href="{{ route('broker.tickets.create', ['policy_no' => $policy['policy_no']]) }}"
           class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
            <i class="bx bx-plus me-1"></i>Open Ticket
        </a>
    </div>
</div>

{{-- Policy summary card --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6 col-md-3">
                <div class="small text-muted">Insured</div>
                <div class="fw-semibold">{{ $policy['name'] }}</div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="small text-muted">Period</div>
                <div class="fw-semibold">
                    {{ \Carbon\Carbon::parse($policy['date_from'])->format('d M Y') }} –
                    {{ \Carbon\Carbon::parse($policy['date_to'])->format('d M Y') }}
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="small text-muted">Sum Insured</div>
                <div class="fw-semibold">₦{{ number_format($policy['actual_si_lc'], 2) }}</div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="small text-muted">Gross Premium</div>
                <div class="fw-semibold">₦{{ number_format($policy['actual_gross_premium_lc'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Linked tickets --}}
<h6 class="fw-bold mb-3">Support Tickets for this Policy</h6>

@if ($tickets->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
        <div class="text-muted">
            <i class="bx bx-message-square-dots" style="font-size:2.5rem;"></i>
            <p class="mt-2 mb-3">No tickets yet for this policy.</p>
            <a href="{{ route('broker.tickets.create', ['policy_no' => $policy['policy_no']]) }}"
               class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
                Open a Ticket
            </a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $t)
                        <tr onclick="window.location='{{ route('broker.tickets.show', $t) }}'" style="cursor:pointer;">
                            <td class="text-muted small">{{ $t->id }}</td>
                            <td class="fw-semibold">{{ $t->subject }}</td>
                            <td><span class="badge {{ $t->priorityColour() }}">{{ ucfirst($t->priority) }}</span></td>
                            <td><span class="badge {{ $t->statusColour() }}">{{ ucwords(str_replace('_', ' ', $t->status)) }}</span></td>
                            <td class="small text-muted">{{ $t->latestMessage?->created_at?->diffForHumans() ?? $t->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

</x-layouts.app>
