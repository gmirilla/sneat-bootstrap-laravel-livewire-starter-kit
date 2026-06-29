<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">My Policies</h4>
        <p class="text-muted small mb-0">Active portfolio from Elite ERP · cached 15 min</p>
    </div>
    <a href="{{ route('broker.tickets.create') }}" class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
        <i class="bx bx-plus me-1"></i>Open Ticket
    </a>
</div>

@if (empty($policies))
    <div class="alert alert-warning">
        <i class="bx bx-info-circle me-2"></i>
        No active policies found in your portfolio. If this is incorrect please contact your account manager.
    </div>
@else
    <div class="row g-3">
        @foreach ($policies as $p)
            @php
                $expired = \Carbon\Carbon::parse($p['date_to'])->isPast();
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;border-left:4px solid {{ $expired ? '#6b7280' : '#B18752' }}!important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="fw-bold" style="color:#161616;font-size:1rem;">{{ $p['policy_no'] }}</div>
                                <div class="small text-muted">{{ $p['product_type'] }}</div>
                            </div>
                            @if ($expired)
                                <span class="badge bg-secondary">Expired</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </div>

                        <table class="table table-sm mb-3" style="font-size:.85rem;">
                            <tr>
                                <td class="text-muted ps-0">Insured</td>
                                <td class="fw-semibold">{{ $p['name'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Period</td>
                                <td>{{ \Carbon\Carbon::parse($p['date_from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($p['date_to'])->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Sum Insured</td>
                                <td class="fw-semibold">₦{{ number_format($p['actual_si_lc'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0 border-0">Premium</td>
                                <td class="border-0">₦{{ number_format($p['actual_gross_premium_lc'], 2) }}</td>
                            </tr>
                        </table>

                        <div class="d-flex gap-2">
                            <a href="{{ route('broker.policies.show', ['policyNo' => $p['policy_no']]) }}"
                               class="btn btn-sm flex-grow-1" style="background:#161616;color:#B18752;font-weight:600;">
                                <i class="bx bx-file me-1"></i>View & Tickets
                            </a>
                            <a href="{{ route('broker.tickets.create', ['policy_no' => $p['policy_no']]) }}"
                               class="btn btn-sm btn-outline-secondary" title="Open ticket for this policy">
                                <i class="bx bx-message-add"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

</x-layouts.app>
