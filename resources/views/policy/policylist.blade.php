<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<style>
    :root {
        --brand:       #B18752;
        --brand-light: #c6ac8b;
        --brand-pale:  #e8f5f1;
        --accent:      #f0a500;
        --danger:      #d63d3d;
        --warning:     #d4a017;
        --surface:     #ffffff;
        --surface-2:   #f7f9f8;
        --border:      #dde8e4;
        --text:        #1a2e28;
        --muted:       #6b8680;
        --radius:      10px;
        --shadow:      0 2px 12px rgba(10,79,60,.08);
        --font:        'DM Sans', sans-serif;
        --mono:        'DM Mono', monospace;
    }

    * { box-sizing: border-box; }

    body, .page-wrap { font-family: var(--font); color: var(--text); }

    /* ── PAGE WRAPPER ─────────────────────────────── */
    .sl-page { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }

    /* ── ALERT ────────────────────────────────────── */
    .sl-alert {
        background: #fff0f0; border-left: 4px solid var(--danger);
        border-radius: var(--radius); padding: .9rem 1.2rem;
        margin-bottom: 1.25rem; font-size: .875rem; color: var(--danger);
    }
    .sl-alert ul { margin: 0; padding-left: 1.2rem; }

    /* ── SECTION CARD ─────────────────────────────── */
    .sl-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .sl-card-header {
        background: var(--brand);
        color: #fff;
        padding: .75rem 1.25rem;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .sl-card-body { padding: 1.25rem; }

    /* ── NOTICE BANNER ────────────────────────────── */
    .sl-notice {
        background: #fff8e6;
        border: 1px solid #f5d87a;
        border-radius: 6px;
        color: #7a5200;
        font-size: .82rem;
        font-weight: 500;
        padding: .6rem 1rem;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    /* ── BUY POLICY GRID ──────────────────────────── */
    .sl-product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 1rem;
    }
    .sl-product-tile {
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        padding: 1.2rem 1rem 1rem;
        text-align: center;
        transition: border-color .2s, box-shadow .2s, transform .15s;
        background: var(--surface-2);
        cursor: pointer;
    }
    .sl-product-tile:hover {
        border-color: var(--brand-light);
        box-shadow: 0 4px 18px rgba(10,79,60,.12);
        transform: translateY(-2px);
    }
    .sl-product-tile .tile-icon {
        font-size: 2.2rem;
        color: var(--brand);
        margin-bottom: .6rem;
        display: block;
    }
    .sl-product-tile p {
        font-size: .78rem;
        color: var(--muted);
        margin: 0 0 .9rem;
        line-height: 1.35;
        min-height: 2rem;
    }
    .sl-product-tile p.text-alert { color: var(--danger); font-weight: 500; }
    .sl-product-tile .btn-tile {
        display: inline-block;
        width: 100%;
        padding: .45rem .6rem;
        font-size: .75rem;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        background: var(--brand);
        color: #fff;
        cursor: pointer;
        transition: background .18s;
    }
    .sl-product-tile .btn-tile:hover:not(:disabled) { background: var(--brand-light); }
    .sl-product-tile .btn-tile:disabled {
        background: #c9d5d2; color: #fff; cursor: not-allowed; opacity: .75;
    }

    /* ── FILTER BAR ───────────────────────────────── */
    .sl-filter-grid {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: flex-end;
    }
    .sl-filter-field { flex: 1 1 160px; }
    .sl-filter-field label {
        display: block;
        font-size: .75rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: .35rem;
    }
    .sl-filter-field select,
    .sl-filter-field input[type="date"] {
        width: 100%;
        padding: .48rem .75rem;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-size: .83rem;
        font-family: var(--font);
        color: var(--text);
        background: var(--surface);
        transition: border-color .2s;
        appearance: none;
        -webkit-appearance: none;
    }
    .sl-filter-field select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236b8680'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right .75rem center;
        padding-right: 2rem;
    }
    .sl-filter-field select:focus,
    .sl-filter-field input[type="date"]:focus {
        outline: none;
        border-color: var(--brand-light);
        box-shadow: 0 0 0 3px rgba(15,122,90,.12);
    }
    .sl-filter-actions { display: flex; gap: .5rem; align-items: flex-end; flex-wrap: wrap; }
    .btn-apply {
        padding: .5rem 1.1rem;
        background: var(--brand);
        color: #fff;
        font-size: .8rem;
        font-weight: 600;
        border: none;
        border-radius: 7px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: background .18s;
        white-space: nowrap;
    }
    .btn-apply:hover { background: var(--brand-light); }
    .btn-reset {
        padding: .5rem 1rem;
        background: transparent;
        color: var(--danger);
        font-size: .8rem;
        font-weight: 600;
        border: 1.5px solid var(--danger);
        border-radius: 7px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: background .18s, color .18s;
        white-space: nowrap;
        text-decoration: none;
    }
    .btn-reset:hover { background: var(--danger); color: #fff; }

    /* ── ACTIVE FILTER CHIPS ──────────────────────── */
    .sl-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .75rem;
        font-size: .78rem;
    }
    .sl-chip-label { color: var(--muted); align-self: center; font-weight: 500; }
    .sl-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: var(--brand-pale);
        color: var(--brand);
        border-radius: 20px;
        padding: .28rem .75rem;
        font-size: .75rem;
        font-weight: 600;
    }

    /* ── STATUS BADGES ────────────────────────────── */
    .sl-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .28rem .7rem;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        margin-bottom: .3rem;
    }
    .badge-approved  { background: #e6f4ee; color: #0a5c35; }
    .badge-draft     { background: #e9eef7; color: #2d4a8a; }
    .badge-failed    { background: #fde8e8; color: #b91c1c; }
    .badge-default   { background: #f3f4f6; color: #4b5563; }
    .badge-niip-ok   { background: #e6f4ee; color: #0a5c35; }
    .badge-niip-warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .badge-niip-err  { background: #fde8e8; color: #b91c1c; }

    /* ── TABLE ────────────────────────────────────── */
    .sl-table-wrap { overflow-x: auto; }
    #policylist {
        width: 100%;
        font-size: .8rem;
        border-collapse: separate;
        border-spacing: 0;
        font-family: var(--font);
    }
    #policylist thead th {
        background: var(--brand);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: .75rem 1rem;
        border: none;
        white-space: nowrap;
    }
    #policylist thead th:first-child { border-radius: 0; }
    #policylist tbody tr { transition: background .15s; }
    #policylist tbody tr:nth-child(even) { background: var(--surface-2); }
    #policylist tbody tr:hover { background: var(--brand-pale); }
    #policylist tbody td {
        padding: .7rem 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text);
    }
    #policylist tfoot td {
        padding: .6rem 1rem;
        font-size: .78rem;
        color: var(--muted);
        border-top: 2px solid var(--border);
        background: var(--surface-2);
    }

    /* policy number mono */
    .policy-no { font-family: var(--mono); font-size: .78rem; color: var(--brand); font-weight: 500; }
    .policy-incomplete { color: var(--muted); font-style: italic; font-size: .75rem; }

    /* action buttons */
    .btn-view {
        padding: .35rem .8rem;
        background: var(--brand);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: .75rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .18s;
    }
    .btn-view:hover { background: var(--brand-light); }
    .btn-cert {
        display: inline-block;
        margin-top: .3rem;
        padding: .35rem .8rem;
        background: transparent;
        color: var(--brand);
        border: 1.5px solid var(--brand);
        border-radius: 6px;
        font-size: .75rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .18s, color .18s;
    }
    .btn-cert:hover { background: var(--brand); color: #fff; }

    /* DataTables override */
    .dataTables_wrapper .dt-buttons .dt-button {
        background: var(--surface);
        border: 1.5px solid var(--border);
        color: var(--text);
        border-radius: 7px;
        font-size: .78rem;
        font-family: var(--font);
        padding: .4rem .9rem;
        transition: background .18s;
    }
    .dataTables_wrapper .dt-buttons .dt-button:hover { background: var(--brand-pale); }
    .dataTables_wrapper .dataTables_filter input {
        border: 1.5px solid var(--border);
        border-radius: 7px;
        padding: .4rem .75rem;
        font-size: .82rem;
        font-family: var(--font);
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: var(--brand-light);
        box-shadow: 0 0 0 3px rgba(15,122,90,.12);
    }
    .dataTables_wrapper { font-family: var(--font); font-size: .82rem; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--brand) !important;
        border-color: var(--brand) !important;
        color: #fff !important;
        border-radius: 6px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--brand-pale) !important;
        border-color: var(--border) !important;
        color: var(--brand) !important;
        border-radius: 6px;
    }

    /* ── MODAL ────────────────────────────────────── */
    .sl-modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(10,30,25,.45); backdrop-filter: blur(3px);
        z-index: 1050; align-items: center; justify-content: center;
    }
    .sl-modal-overlay.active { display: flex; }
    .sl-modal {
        background: var(--surface);
        border-radius: 12px;
        box-shadow: 0 16px 48px rgba(0,0,0,.18);
        width: 90%; max-width: 500px;
        animation: modalIn .22s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-14px) scale(.97); }
        to   { opacity: 1; transform: none; }
    }
    .sl-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
    }
    .sl-modal-header h5 { margin: 0; font-size: .95rem; font-weight: 700; color: var(--brand); }
    .sl-modal-close {
        background: none; border: none; cursor: pointer;
        color: var(--muted); font-size: 1.1rem;
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s;
    }
    .sl-modal-close:hover { background: var(--surface-2); }
    .sl-modal-body { padding: 1.1rem 1.25rem; font-size: .87rem; line-height: 1.6; color: var(--text); }
    .sl-modal-footer {
        padding: .85rem 1.25rem;
        border-top: 1px solid var(--border);
        display: flex; justify-content: flex-end;
    }
    .btn-modal-close {
        padding: .45rem 1.2rem;
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-size: .82rem; font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }
    .btn-modal-close:hover { background: var(--border); }
</style>

<x-layouts.app>

    @if ($errors->any())
        <div class="sl-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="sl-page">

        {{-- ── BUY A POLICY ─────────────────────────────────────── --}}
        <div class="sl-card">
            <div class="sl-card-header">
                <i class="fa-solid fa-shield-halved"></i> Buy a Salam Policy
            </div>
            <div class="sl-card-body">
                <div class="sl-notice">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Purchase of Third Party Insurance for Trucks, Lorries and Articulated Vehicles is <strong>not allowed</strong> via this app.
                </div>

                <form action="{{ route('buy_policy') }}" method="get">
                    <div class="sl-product-grid">

                        <div class="sl-product-tile">
                            <span class="tile-icon"><i class="fa-solid fa-car"></i></span>
                            <p>Private Vehicles</p>
                            <button class="btn-tile" name="btnprivatemotor">Private Motor Third Party</button>
                        </div>

                        <div class="sl-product-tile">
                            <span class="tile-icon"><i class="fa-solid fa-truck"></i></span>
                            <p>Taxis, Staff Bus, Mini Bus</p>
                            <button class="btn-tile" name="btncommercialmotor">Commercial Motor Third Party</button>
                        </div>

                        <div class="sl-product-tile">
                            <span class="tile-icon"><i class="fa-solid fa-motorcycle"></i></span>
                            <p>Motorcycle, Tricycle</p>
                            <button class="btn-tile" name="btnmotorcycle">Motorcycle/Tricycle Third Party</button>
                        </div>

                        <div class="sl-product-tile">
                            <span class="tile-icon"><i class="fa-solid fa-building-shield"></i></span>
                            <p class="text-alert">Occupier's Liability Policy**</p>
                            <button class="btn-tile" name="btnoccupier" disabled>Occupier's Liability</button>
                        </div>

                        <div class="sl-product-tile">
                            <span class="tile-icon"><i class="fa-solid fa-money"></i></span>
                            <p class="text-alert">Salam Savings Policy**</p>
                            <button class="btn-tile" name="btnsipp" disabled>Salam Investment Plan</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- ── FILTER BAR ───────────────────────────────────────── --}}
        <div class="sl-card">
            <div class="sl-card-header">
                <i class="fa-solid fa-sliders"></i> Filter Policies
            </div>
            <div class="sl-card-body">
                <form action="{{ route('filterreport') }}" method="post" id="filterForm">
                    @csrf
                    <div class="sl-filter-grid">

                        {{-- Policy Type --}}
                        <div class="sl-filter-field">
                            <label for="policytype">Policy Type</label>
                            <select name="policytype" id="policytype">
                                @if ($searchParams['policytype'] ?? false)
                                    <option value="{{ $searchParams['policytype'] }}" selected>{{ ucwords($searchParams['policytype']) }}</option>
                                @else
                                    <option value="">All Types</option>
                                    @forelse ($products as $product)
                                        <option value="{{ $product }}">{{ ucwords($product) }}</option>
                                    @empty
                                    @endforelse
                                @endif
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="sl-filter-field">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                @if ($searchParams['status'] ?? false)
                                    <option value="{{ $searchParams['status'] }}" selected>{{ ucwords($searchParams['status']) }}</option>
                                @else
                                    <option value="">All Statuses</option>
                                    <option value="approved">Approved</option>
                                    <option value="draft">Draft</option>
                                    <option value="failed">Failed</option>
                                @endif
                            </select>
                        </div>

                        {{-- Date From --}}
                        <div class="sl-filter-field">
                            <label for="datefrom">Date From</label>
                            @if ($searchParams['datefrom'] ?? false)
                                <input type="date" name="datefromfilter" id="datefromfilter" value="{{ $searchParams['datefrom'] }}" disabled>
                                <input type="date" name="datefrom" id="datefrom" value="{{ $searchParams['datefrom'] }}" hidden>
                            @else
                                <input type="date" name="datefrom" id="datefrom">
                            @endif
                        </div>

                        {{-- Date To --}}
                        <div class="sl-filter-field">
                            <label for="dateto">Date To</label>
                            @if ($searchParams['dateto'] ?? false)
                                <input type="date" name="datetofilter" id="datetofilter" value="{{ $searchParams['dateto'] }}" disabled>
                                <input type="date" name="dateto" id="dateto" value="{{ $searchParams['dateto'] }}" hidden>
                            @else
                                <input type="date" name="dateto" id="dateto">
                            @endif
                        </div>

                        {{-- Agent (admin only) --}}
                        @if ($user->role == 'admin' || $user->role == 'superadmin')
                        <div class="sl-filter-field">
                            <label for="agentcode">Agent</label>
                            <select name="agentcode" id="agentcode">
                                <option value="">All Agents</option>
                                @forelse ($agentslist as $agent)
                                    <option value="{{ $agent->uid }}" @if(($searchParams['agentcode'] ?? '') == $agent->uid) selected @endif>
                                        {{ $agent->getuserinfo()->name ?? 'Unknown' }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="sl-filter-actions">
                            <button class="btn-apply" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i> Apply Filters
                            </button>
                            <a href="{{ route('filterreport') }}" class="btn-reset">
                                <i class="fa-solid fa-xmark"></i> Clear Filters
                            </a>
                        </div>

                    </div>

                    {{-- Active Filter Chips --}}
                    @if (!empty($searchParams))
                    <div class="sl-filter-chips">
                        <span class="sl-chip-label">Active filters:</span>
                        @if (!empty($searchParams['policytype']))
                            <span class="sl-chip"><i class="fa-solid fa-tag"></i> {{ ucwords($searchParams['policytype']) }}</span>
                        @endif
                        @if (!empty($searchParams['status']))
                            <span class="sl-chip"><i class="fa-solid fa-circle-dot"></i> {{ ucwords($searchParams['status']) }}</span>
                        @endif
                        @if (!empty($searchParams['datefrom']))
                            <span class="sl-chip"><i class="fa-regular fa-calendar"></i> From {{ $searchParams['datefrom'] }}</span>
                        @endif
                        @if (!empty($searchParams['dateto']))
                            <span class="sl-chip"><i class="fa-regular fa-calendar"></i> To {{ $searchParams['dateto'] }}</span>
                        @endif
                        @if (!empty($searchParams['agentcode']))
                            <span class="sl-chip"><i class="fa-solid fa-user"></i> Agent filtered</span>
                        @endif
                    </div>
                    @endif

                </form>
            </div>
        </div>

        {{-- ── POLICY TABLE ─────────────────────────────────────── --}}
        <div class="sl-card">
            <div class="sl-card-header">
                <i class="fa-solid fa-list-ul"></i> Policy List
            </div>
            <div class="sl-card-body">
                <div class="sl-table-wrap">
                    <table class="table table-striped table-sm" id="policylist">
                        <thead>
                            <tr>
                                <th>Policy No.</th>
                                <th>Policy Type</th>
                                <th>Risk / Reg No.</th>
                                <th>Insured Name</th>
                                <th>Contribution</th>
                                <th>Created At</th>
                                <th>Status</th>
                                @if ($user->role == 'admin' || $user->role == 'superadmin')
                                    <th>Agent</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <td colspan="{{ ($user->role == 'admin' || $user->role == 'superadmin') ? 9 : 8 }}">
                                    @if (!empty($searchParams))
                                        <strong>Filtered by:</strong>
                                        @if (!empty($searchParams['policytype'])) Policy Type: <strong>{{ ucwords($searchParams['policytype']) }}</strong>; @endif
                                        @if (!empty($searchParams['status'])) Status: <strong>{{ ucwords($searchParams['status']) }}</strong>; @endif
                                        @if (!empty($searchParams['datefrom'])) Date From: <strong>{{ $searchParams['datefrom'] }}</strong> @endif
                                        @if (!empty($searchParams['dateto'])) To: <strong>{{ $searchParams['dateto'] }}</strong> @endif
                                    @endif
                                </td>
                            </tr>
                        </tfoot>

                        <tbody>
                            @forelse ($policies as $policy)
                                <tr>
                                    <td>
                                        @if (empty($policy->policyno))
                                            <span class="policy-incomplete">Incomplete</span>
                                        @else
                                            <span class="policy-no">{{ $policy->policyno }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $policy->producttype }}</td>
                                    <td>
                                        @if (empty($policy->getrisk()->regno))
                                            <span class="policy-incomplete">No Reg No. #{{ $policy->id }}</span>
                                        @else
                                            {{ $policy->getrisk()->regno }}
                                        @endif
                                    </td>
                                    <td>{{ $policy->insured_name }}</td>
                                    <td>{{ $policy->contribution }}</td>
                                    <td>{{ $policy->created_at }}</td>
                                    <td>
                                        @switch($policy->status)
                                            @case('approved')
                                                <span class="sl-badge badge-approved"><i class="fa-solid fa-circle-check"></i> Approved</span><br>
                                                @if (Str::contains(strtolower($policy->producttype), 'motor'))
                                                    @php $niip = $policy->getniipstatus(); @endphp
                                                    @if (is_array($niip) && ($niip['isSuccess'] ?? false) === true)
                                                        <a href="#" class="niip-trigger" data-message="{{ $policy->niip_status }}">
                                                            <span class="sl-badge badge-niip-ok"><i class="fa-solid fa-check"></i> NIIP OK</span>
                                                        </a>
                                                    @elseif (is_array($niip) && ($niip['statusCode'] ?? '') === '11')
                                                        <a href="#" class="niip-trigger" data-message="{{ $policy->niip_status }}">
                                                            <span class="sl-badge badge-niip-warn"><i class="fa-solid fa-triangle-exclamation"></i> Possible Issue</span>
                                                        </a>
                                                    @else
                                                        <a href="#" class="niip-trigger" data-message="{{ $policy->niip_status }}">
                                                            <span class="sl-badge badge-niip-err"><i class="fa-solid fa-circle-xmark"></i> NIIP Issue</span>
                                                        </a>
                                                    @endif
                                                @endif
                                            @break
                                            @case('draft')
                                                <span class="sl-badge badge-draft"><i class="fa-regular fa-clock"></i> Draft</span>
                                            @break
                                            @case('failed')
                                                <span class="sl-badge badge-failed"><i class="fa-solid fa-circle-xmark"></i> Failed</span>
                                            @break
                                            @default
                                                <span class="sl-badge badge-default">{{ $policy->status }}</span>
                                        @endswitch
                                    </td>

                                    @if ($user->role == 'admin' || $user->role == 'superadmin')
                                        <td>{{ $policy->getagentname() }}</td>
                                    @endif

                                    <td>
                                        <form action="{{ route('view_policy') }}" method="get">
                                            <input type="number" value="{{ $policy->id }}" hidden name="id">
                                            <button class="btn-view" type="submit">
                                                <i class="fa-solid fa-eye"></i> View
                                            </button>
                                        </form>
                                        @if ($policy->status == 'approved' && !empty($policy->policyno))
                                            <a target="_blank" class="btn-cert"
                                                href="http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no={{ $policy->policyno }}">
                                                <i class="fa-solid fa-file-certificate"></i> Certificate
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /sl-page --}}

    {{-- ── NIIP MODAL ──────────────────────────────────────────── --}}
    <div class="sl-modal-overlay" id="niipModal">
        <div class="sl-modal">
            <div class="sl-modal-header">
                <h5><i class="fa-solid fa-circle-info"></i> NIIP Status</h5>
                <button class="sl-modal-close" id="niipModalClose" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="sl-modal-body" id="niipModalMessage"></div>
            <div class="sl-modal-footer">
                <button class="btn-modal-close" id="niipModalCloseFooter">Close</button>
            </div>
        </div>
    </div>

    <script>
        // NIIP modal
        const niipModal      = document.getElementById('niipModal');
        const niipMsgEl      = document.getElementById('niipModalMessage');
        const closeButtons   = [
            document.getElementById('niipModalClose'),
            document.getElementById('niipModalCloseFooter')
        ];

        document.querySelectorAll('.niip-trigger').forEach(el => {
            el.addEventListener('click', e => {
                e.preventDefault();
                niipMsgEl.textContent = el.getAttribute('data-message');
                niipModal.classList.add('active');
            });
        });
        closeButtons.forEach(btn => btn.addEventListener('click', () => niipModal.classList.remove('active')));
        niipModal.addEventListener('click', e => { if (e.target === niipModal) niipModal.classList.remove('active'); });

        // DataTable
        new DataTable('#policylist', {
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel"></i> Excel', title: 'Policy List' },
                { extend: 'pdfHtml5',   text: '<i class="fa-solid fa-file-pdf"></i> PDF',   title: 'Policy List', orientation: 'landscape', pageSize: 'A4' }
            ]
        });
    </script>

</x-layouts.app>