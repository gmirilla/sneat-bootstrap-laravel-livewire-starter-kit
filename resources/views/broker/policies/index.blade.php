<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="fw-bold mb-0">My Policies</h4>
        <p class="text-muted small mb-0">Portfolio from Elite ERP · cached 15 min</p>
    </div>
    <a href="{{ route('broker.tickets.create') }}" class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
        <i class="bx bx-plus me-1"></i>Open Ticket
    </a>
</div>

{{-- ── Filter pills ── --}}
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="{{ route('broker.policies') }}"
       class="btn btn-sm {{ $view === 'all' ? '' : 'btn-outline-secondary' }}"
       style="{{ $view === 'all' ? 'background:#161616;color:#B18752;font-weight:700;' : '' }}">
        All
        <span class="badge ms-1 {{ $view === 'all' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $counts['all'] }}</span>
    </a>
    <a href="{{ route('broker.policies', ['view' => 'active']) }}"
       class="btn btn-sm {{ $view === 'active' ? '' : 'btn-outline-secondary' }}"
       style="{{ $view === 'active' ? 'background:#161616;color:#B18752;font-weight:700;' : '' }}">
        Active
        <span class="badge ms-1 {{ $view === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $counts['active'] }}</span>
    </a>
    <a href="{{ route('broker.policies', ['view' => 'expiring']) }}"
       class="btn btn-sm {{ $view === 'expiring' ? '' : 'btn-outline-secondary' }}"
       style="{{ $view === 'expiring' ? 'background:#161616;color:#B18752;font-weight:700;' : '' }}">
        Expiring Soon
        <span class="badge ms-1 {{ $view === 'expiring' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $counts['expiring'] }}</span>
    </a>

    @if ($view !== 'all')
        <span class="text-muted small ms-2">
            Showing {{ count($policies) }} {{ count($policies) === 1 ? 'policy' : 'policies' }}
            &nbsp;·&nbsp;
            <a href="{{ route('broker.policies') }}" class="text-decoration-none">Clear</a>
        </span>
    @endif
</div>

@if (empty($policies))
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
        <div class="text-muted">
            <i class="bx bx-file-blank" style="font-size:2.5rem;"></i>
            @if ($view === 'expiring')
                <p class="mt-2 mb-1 fw-semibold">No policies expiring in the next 30 days.</p>
                <p class="small"><a href="{{ route('broker.policies') }}">View all policies</a></p>
            @elseif ($view === 'active')
                <p class="mt-2 mb-1 fw-semibold">No active policies found.</p>
                <p class="small"><a href="{{ route('broker.policies') }}">View all policies</a></p>
            @else
                <p class="mt-2 mb-0">No policies found in your portfolio.</p>
                <p class="small text-muted">If this is incorrect please contact your account manager.</p>
            @endif
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($policies as $p)
            @php
                $expired  = \Carbon\Carbon::parse($p['date_to'])->isPast();
                $expiring = !$expired && \Carbon\Carbon::parse($p['date_to'])->lte(\Carbon\Carbon::today()->addDays(30));
                $borderColor = $expired ? '#6b7280' : ($expiring ? '#d97706' : '#B18752');
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;border-left:4px solid {{ $borderColor }}!important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="fw-bold" style="color:#161616;font-size:1rem;">{{ $p['policy_no'] }}</div>
                                <div class="small text-muted">{{ $p['product_type'] }}</div>
                            </div>
                            @if ($expired)
                                <span class="badge bg-secondary">Expired</span>
                            @elseif ($expiring)
                                <span class="badge bg-warning text-dark">Expiring Soon</span>
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

                        @if ($expiring)
                            <div class="small text-warning fw-semibold mb-2">
                                <i class="bx bx-time-five me-1"></i>
                                Expires {{ \Carbon\Carbon::parse($p['date_to'])->diffForHumans() }}
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <a href="{{ route('broker.policies.show', ['policyNo' => $p['policy_no']]) }}"
                               class="btn btn-sm flex-grow-1" style="background:#161616;color:#B18752;font-weight:600;">
                                <i class="bx bx-file me-1"></i>View & Tickets
                            </a>
                            <a href="{{ route('broker.claims.create', ['policy_no' => $p['policy_no']]) }}"
                               class="btn btn-sm btn-outline-danger" title="Submit claim notification">
                                <i class="bx bx-bell-plus"></i>
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
