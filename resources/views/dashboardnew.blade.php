<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
@section('title', __('Dashboard'))
@php
    use Illuminate\Support\Facades\Auth;
    $user = Auth::user();
    $isAdmin = in_array($user->role, ['admin', 'superadmin']);
@endphp

<x-layouts.app :title="__('Dashboard')">

    <div class="dash-wrapper">

        {{-- ── Page header ── --}}
        <div class="page-header">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
            </div>
            <div>
                <h1>Dashboard</h1>
                <p>Overview of your policy portfolio and activity.</p>
            </div>
        </div>

        {{-- ── Broker quick-links (replaces filters for brokers) ── --}}
        @if ($user->role === 'broker')
        <div class="d-flex gap-3 mb-4 flex-wrap">
            <a href="{{ route('broker.policies') }}" class="btn btn-sm px-4 py-2 fw-semibold"
               style="background:#161616;color:#B18752;border-radius:10px;">
                <i class="bx bx-file-blank me-1"></i>My Policies
            </a>
            <a href="{{ route('broker.tickets') }}" class="btn btn-sm px-4 py-2 fw-semibold"
               style="background:#161616;color:#B18752;border-radius:10px;">
                <i class="bx bx-support me-1"></i>My Tickets
            </a>
            @if (!($brokerApiAvailable ?? true))
                <span class="badge bg-warning text-dark align-self-center ms-2">
                    <i class="bx bx-wifi-off me-1"></i>Policy data unavailable
                </span>
            @endif
        </div>
        @endif

        {{-- ── Filters (hidden for brokers) ── --}}
        @if ($user->role !== 'broker')
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <h2>Filters</h2>
            </div>
            <div class="section-body">
                <form action="{{ route('dashboard') }}" method="GET">
                    <div class="filter-grid">

                        {{-- Policy Type --}}
                        <div class="filter-group">
                            <label class="filter-label" for="policytype">Policy Type</label>
                            <select name="policytype" id="policytype" class="filter-select">
                                <option value="">All Types</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product }}"
                                        {{ ($searchParams['policytype'] ?? '') == $product ? 'selected' : '' }}>
                                        {{ ucwords($product) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="filter-group">
                            <label class="filter-label" for="status">Status</label>
                            <select name="status" id="status" class="filter-select">
                                <option value="">All Statuses</option>
                                <option value="approved"
                                    {{ ($searchParams['status'] ?? '') == 'approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="draft"
                                    {{ ($searchParams['status'] ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="failed"
                                    {{ ($searchParams['status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed
                                </option>
                            </select>
                        </div>

                        {{-- Date From --}}
                        <div class="filter-group">
                            <label class="filter-label" for="datefrom">Date From</label>
                            <input type="date" name="datefrom" id="datefrom" class="filter-input"
                                value="{{ $searchParams['datefrom'] ?? '' }}"
                                {{ !empty($searchParams['datefrom']) ? 'readonly' : '' }}>
                        </div>

                        {{-- Date To --}}
                        <div class="filter-group">
                            <label class="filter-label" for="dateto">Date To</label>
                            <input type="date" name="dateto" id="dateto" class="filter-input"
                                value="{{ $searchParams['dateto'] ?? '' }}"
                                {{ !empty($searchParams['dateto']) ? 'readonly' : '' }}>
                        </div>

                        {{-- Agent (admin only) --}}
                        @if ($isAdmin)
                            <div class="filter-group">
                                <label class="filter-label" for="agentcode">Agent</label>
                                <select name="agentcode" id="agentcode" class="filter-select">
                                    <option value="">All Agents</option>
                                    @foreach ($agentslist as $agent)
                                        <option value="{{ $agent->uid }}"
                                            {{ ($searchParams['agentcode'] ?? '') == $agent->uid ? 'selected' : '' }}>
                                            {{ $agent->getuserinfo()->name ?? 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="filter-group">
                            <label class="filter-label" style="visibility:hidden">Actions</label>
                            <div class="filter-actions">
                                <button class="btn-apply" type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z" />
                                    </svg>
                                    Apply
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn-clear">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Clear
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Active filter chips --}}
            @if (!empty(array_filter($searchParams ?? [])))
                <div class="active-filters">
                    <span class="active-filters-label">Filtered by</span>

                    @if (!empty($searchParams['policytype']))
                        <span class="filter-chip">Type <span>{{ ucwords($searchParams['policytype']) }}</span></span>
                    @endif
                    @if (!empty($searchParams['status']))
                        <span class="filter-chip">Status <span>{{ ucwords($searchParams['status']) }}</span></span>
                    @endif
                    @if (!empty($searchParams['datefrom']))
                        <span class="filter-chip">From <span>{{ $searchParams['datefrom'] }}</span></span>
                    @endif
                    @if (!empty($searchParams['dateto']))
                        <span class="filter-chip">To <span>{{ $searchParams['dateto'] }}</span></span>
                    @endif
                    @if (!empty($searchParams['agentcode']))
                        <span class="filter-chip">Agent <span>{{ $searchParams['agentname'] ?? '—' }}</span></span>
                    @endif
                </div>
            @endif
        </div>
        @endif {{-- end @if role !== broker --}}

        {{-- ══════════════════════════════════════════
             BROKER DASHBOARD
        ══════════════════════════════════════════ --}}
        @if ($user->role === 'broker')

        {{-- Broker stat cards --}}
        <div class="stats-grid" style="--cols:4">

            <div class="stat-card" style="animation-delay:.05s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-blue">
                        <i class="bx bx-file-blank" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="stat-value stat-value-blue">{{ $brokerTotalPolicies ?? 0 }}</div>
                </div>
                <div class="stat-card-footer">Total Policies</div>
            </div>

            <a class="stat-card" href="{{ route('broker.policies', ['view' => 'active']) }}" style="animation-delay:.08s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-green">
                        <i class="bx bx-check-shield" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="stat-value stat-value-green">{{ $brokerActivePolicies ?? 0 }}</div>
                </div>
                <div class="stat-card-footer">Active Policies →</div>
            </a>

            <div class="stat-card" style="animation-delay:.11s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-purple">
                        <i class="bx bx-money" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="stat-value stat-value-purple" style="font-size:1.1rem;">
                        ₦{{ number_format($brokerActivePremium ?? 0, 2) }}
                    </div>
                </div>
                <div class="stat-card-footer">Active Premium (₦)</div>
            </div>

            <a class="stat-card" href="{{ route('broker.policies', ['view' => 'expiring']) }}" style="animation-delay:.17s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-amber">
                        <i class="bx bx-time-five" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="stat-value stat-value-amber">{{ $brokerExpiring30 ?? 0 }}</div>
                </div>
                <div class="stat-card-footer">Expiring in 30 Days →</div>
            </a>

            <a class="stat-card" href="{{ route('broker.tickets') }}" style="animation-delay:.20s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-red">
                        <i class="bx bx-support" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="stat-value stat-value-red">{{ $brokerOpenTickets ?? 0 }}</div>
                </div>
                <div class="stat-card-footer">Open Tickets →</div>
            </a>

        </div>

        {{-- Broker charts --}}
        @if (!empty($brokerPortfolioByType) || !empty($brokerExpiryByMonth))
        <div class="row g-4 mt-1 mb-4">

            {{-- Doughnut: Portfolio Mix --}}
            <div class="col-md-5">
                <div class="section-card h-100">
                    <div class="section-header">
                        <i class="bx bx-pie-chart-alt-2" style="font-size:1rem;color:#B18752;"></i>
                        <h2>Portfolio Mix</h2>
                    </div>
                    <div class="section-body d-flex align-items-center justify-content-center" style="min-height:260px;">
                        <canvas id="portfolioChart" style="max-height:260px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Bar: Upcoming Expirations --}}
            <div class="col-md-7">
                <div class="section-card h-100">
                    <div class="section-header">
                        <i class="bx bx-bar-chart-alt-2" style="font-size:1rem;color:#B18752;"></i>
                        <h2>Upcoming Expirations (6 months)</h2>
                    </div>
                    <div class="section-body" style="min-height:260px;">
                        <canvas id="expiryChart" style="max-height:260px;width:100%;"></canvas>
                    </div>
                </div>
            </div>

        </div>
        @else
            <div class="alert alert-warning mt-3">
                <i class="bx bx-wifi-off me-2"></i>
                Portfolio data could not be loaded from Elite ERP. Please try again later or contact your administrator.
            </div>
        @endif

        @endif {{-- end broker dashboard --}}

        {{-- ── Stat cards (non-broker) ── --}}
        @if ($user->role !== 'broker')
        <div class="stats-grid">
            <div class="stat-card" style="animation-delay:.05s">

                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <div class="stat-value stat-value-blue">{{ $creditassigned }}</div>
                </div>
                <div class="stat-card-footer">Total Credits Assigned</div>
            </div>
            @if (in_array($user->role, ['admin', 'superadmin']))
                <div class="stat-card" style="animation-delay:.05s">
                    <div class="stat-card-body">
                        <div class="stat-icon stat-icon-red">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </div>
                        <div class="stat-value stat-value-red">{{ $creditused }}</div>
                    </div>
                    <div class="stat-card-footer">Total Credits Used</div>
                </div>

                <div class="stat-card" style="animation-delay:.05s">
                    <div class="stat-card-body">
                        <div class="stat-icon stat-icon-green">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </div>
                        <div class="stat-value stat-value-green">{{ $creditleft }}</div>
                    </div>
                    <div class="stat-card-footer">Upload Credits Left</div>
                </div>
            @endif

            <div class="stat-card" style="animation-delay:.08s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div class="stat-value stat-value-blue">{{ $totalpolcount }}</div>
                </div>
                <div class="stat-card-footer">Active Policies</div>
            </div>

            <div class="stat-card" style="animation-delay:.11s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-amber">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                    </div>
                    <div class="stat-value stat-value-amber">{{ $totalpoldraft }}</div>
                </div>
                <div class="stat-card-footer">Draft Policies</div>
            </div>

            <div class="stat-card" style="animation-delay:.14s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="stat-value stat-value-red">{{ $totalpolfailed }}</div>
                </div>
                <div class="stat-card-footer">Failed Policies</div>
            </div>

            <div class="stat-card" style="animation-delay:.17s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-value stat-value-green">{{ $totalpolapproved }}</div>
                </div>
                <div class="stat-card-footer">Approved Policies</div>
            </div>

            <a class="stat-card" href="{{ route('renewalslist') }}" style="animation-delay:.20s">
                <div class="stat-card-body">
                    <div class="stat-icon stat-icon-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    <div class="stat-value stat-value-purple">{{ $approachingrenewal }}</div>
                </div>
                <div class="stat-card-footer">Upcoming Renewals →</div>
            </a>

        </div>
        @endif {{-- end @if role !== broker --}}

        {{-- ── Tables (non-broker only) ── --}}
        @if ($user->role !== 'broker')
        @if ($isAdmin)
            <div class="tables-grid">

                {{-- Sales by Type --}}
                <div class="section-card table-card">
                    <div class="section-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <h2>Sales by Type</h2>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product Type</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($policygroup as $pgdata)
                                    <tr>
                                        <td>{{ $pgdata->producttype }}</td>
                                        <td><span class="count-pill">{{ $pgdata->total }}</span></td>
                                    </tr>
                                @empty
                                    <tr class="empty-row">
                                        <td colspan="2">No sales to report.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        @else
            {{-- Non-admin: Sales by Type only --}}
            <div class="section-card table-card" style="max-width:460px">
                <div class="section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    <h2>Sales by Type</h2>
                </div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Type</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($policygroup as $pgdata)
                                <tr>
                                    <td>{{ $pgdata->producttype }}</td>
                                    <td><span class="count-pill">{{ $pgdata->total }}</span></td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="2">You have no sales to report yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        @endif
        @endif {{-- end @if role !== broker --}}

    </div>

    {{-- Chart.js (broker only) --}}
    @if ($user->role === 'broker' && !empty($brokerPortfolioByType))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const gold  = '#B18752';
        const dark  = '#161616';
        const palette = [
            '#B18752','#2563eb','#16a34a','#dc2626','#7c3aed',
            '#0891b2','#d97706','#db2777','#65a30d','#9333ea'
        ];

        // ── Doughnut: Portfolio Mix ──
        const portfolioData = @json(array_values($brokerPortfolioByType));
        const portfolioLabels = @json(array_keys($brokerPortfolioByType));

        new Chart(document.getElementById('portfolioChart'), {
            type: 'doughnut',
            data: {
                labels: portfolioLabels,
                datasets: [{
                    data: portfolioData,
                    backgroundColor: palette.slice(0, portfolioLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8,
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} polic${ctx.parsed === 1 ? 'y' : 'ies'}`
                        }
                    }
                },
                cutout: '62%',
                maintainAspectRatio: true,
            }
        });

        // ── Bar: Upcoming Expirations ──
        const expiryLabels = @json(array_keys($brokerExpiryByMonth));
        const expiryData   = @json(array_values($brokerExpiryByMonth));

        new Chart(document.getElementById('expiryChart'), {
            type: 'bar',
            data: {
                labels: expiryLabels,
                datasets: [{
                    label: 'Policies Expiring',
                    data: expiryData,
                    backgroundColor: expiryData.map((v, i) => i === 0 ? '#dc2626' : gold),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} polic${ctx.parsed.y === 1 ? 'y' : 'ies'} expiring`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#f0f0f0' }
                    },
                    x: { grid: { display: false } }
                },
                maintainAspectRatio: true,
            }
        });
    })();
    </script>
    @endif

    <script>
        // Only initialise DataTable if the table exists (admin view)
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('agentproduction');
            if (table && typeof DataTable !== 'undefined') {
                new DataTable('#agentproduction', {
                    pageLength: 10,
                    language: {
                        search: 'Search agents:'
                    }
                });
            }
        });

        // Allow editing readonly date filters on click
        document.querySelectorAll('.filter-input[readonly]').forEach(function(input) {
            input.addEventListener('click', function() {
                this.removeAttribute('readonly');
                this.focus();
            });
        });
    </script>
</x-layouts.app>
