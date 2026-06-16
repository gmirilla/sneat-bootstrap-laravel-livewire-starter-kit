@push('styles')
<style>
    :root {
        --brand: #B18752;
        --brand-light: #c6ac8b;
        --brand-pale: #f6ede0;
        /* FIX #6: warm tint matching gold brand, was green #e8f5f1 */
        --accent: #f0a500;
        --danger: #d63d3d;
        --warning: #d4a017;
        --surface: #ffffff;
        --surface-2: #f7f9f8;
        --border: #dde8e4;
        --text: #1a2e28;
        --muted: #6b8680;
        --radius: 10px;
        --shadow: 0 2px 12px rgba(10, 79, 60, .08);
        --font: 'DM Sans', sans-serif;
        --mono: 'DM Mono', monospace;
    }

    * {
        box-sizing: border-box;
    }

    body,
    .page-wrap {
        font-family: var(--font);
        color: var(--text);
    }

    /* ── PAGE WRAPPER ─────────────────────────────── */
    .sl-page {
        padding: 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ── ALERT ────────────────────────────────────── */
    .sl-alert {
        background: #fff0f0;
        border-left: 4px solid var(--danger);
        border-radius: var(--radius);
        padding: .9rem 1.2rem;
        margin-bottom: 1.25rem;
        font-size: .875rem;
        color: var(--danger);
    }

    .sl-alert ul {
        margin: 0;
        padding-left: 1.2rem;
    }

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

    .sl-card-body {
        padding: 1.25rem;
    }

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
        box-shadow: 0 4px 18px rgba(177, 135, 82, .15);
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

    .sl-product-tile p.text-alert {
        color: var(--danger);
        font-weight: 500;
    }

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

    .sl-product-tile .btn-tile:hover:not(:disabled) {
        background: var(--brand-light);
    }

    .sl-product-tile .btn-tile:disabled {
        background: #c9d5d2;
        color: #fff;
        cursor: not-allowed;
        opacity: .75;
    }

    /* ── FILTER BAR ───────────────────────────────── */
    .sl-filter-grid {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: flex-end;
    }

    .sl-filter-field {
        flex: 1 1 160px;
    }

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
        box-shadow: 0 0 0 3px rgba(177, 135, 82, .15);
    }

    .sl-filter-actions {
        display: flex;
        gap: .5rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }

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

    .btn-apply:hover {
        background: var(--brand-light);
    }

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

    .btn-reset:hover {
        background: var(--danger);
        color: #fff;
    }

    /* ── ACTIVE FILTER CHIPS ──────────────────────── */
    .sl-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .75rem;
        font-size: .78rem;
    }

    .sl-chip-label {
        color: var(--muted);
        align-self: center;
        font-weight: 500;
    }

    .sl-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: var(--brand-pale);
        /* now correctly warm gold tint */
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

    .badge-approved {
        background: #e6f4ee;
        color: #0a5c35;
    }

    .badge-draft {
        background: #e9eef7;
        color: #2d4a8a;
    }

    .badge-failed {
        background: #fde8e8;
        color: #b91c1c;
    }

    /* FIX #5: dedicated cancelled badge — neutral grey/amber, distinct from failed */
    .badge-cancelled {
        background: #f3f0e8;
        color: #7a6030;
        border: 1px solid #e0ceaa;
    }

    .badge-default {
        background: #f3f4f6;
        color: #4b5563;
    }

    .badge-niip-ok {
        background: #e6f4ee;
        color: #0a5c35;
    }

    .badge-niip-warn {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .badge-niip-err {
        background: #fde8e8;
        color: #b91c1c;
    }

    .badge-emcr {
        background: #eaf2ff;
        color: #1a56a6;
        border: 1px solid #c3d9f7;
    }

    /* ── TABLE ────────────────────────────────────── */
    .sl-table-wrap {
        overflow-x: auto;
    }

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

    #policylist tbody tr {
        transition: background .15s;
    }

    #policylist tbody tr:nth-child(even) {
        background: var(--surface-2);
    }

    #policylist tbody tr:hover {
        background: var(--brand-pale);
    }

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

    .policy-no {
        font-family: var(--mono);
        font-size: .78rem;
        color: var(--brand);
        font-weight: 500;
    }

    .policy-incomplete {
        color: var(--muted);
        font-style: italic;
        font-size: .75rem;
    }

    /* ── ACTION BUTTONS ───────────────────────────── */
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

    .btn-view:hover {
        background: var(--brand-light);
    }

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

    .btn-cert:hover {
        background: var(--brand);
        color: #fff;
    }

    .btn-cancel {
        display: inline-block;
        margin-top: .3rem;
        padding: .35rem .8rem;
        background: transparent;
        color: var(--danger);
        border: 1.5px solid var(--danger);
        border-radius: 6px;
        font-size: .75rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .18s, color .18s;
    }

    .btn-cancel:hover {
        background: var(--danger);
        color: #fff;
    }

    /* ── MODAL ────────────────────────────────────── */
    .sl-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(10, 30, 25, .45);
        backdrop-filter: blur(3px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }

    .sl-modal-overlay.active {
        display: flex;
    }

    .sl-modal {
        background: var(--surface);
        border-radius: 12px;
        box-shadow: 0 16px 48px rgba(0, 0, 0, .18);
        width: 90%;
        max-width: 500px;
        animation: modalIn .22s ease;
    }

    @keyframes modalIn {
        from {
            opacity: 0;
            transform: translateY(-14px) scale(.97);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .sl-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
    }

    .sl-modal-header h5 {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: var(--brand);
    }

    .sl-modal-header.danger h5 {
        color: var(--danger);
    }

    .sl-modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        font-size: 1.1rem;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
    }

    .sl-modal-close:hover {
        background: var(--surface-2);
    }

    .sl-modal-body {
        padding: 1.1rem 1.25rem;
        font-size: .87rem;
        line-height: 1.6;
        color: var(--text);
    }

    .sl-modal-footer {
        padding: .85rem 1.25rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
    }

    .btn-modal-close {
        padding: .45rem 1.2rem;
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }

    .btn-modal-close:hover {
        background: var(--border);
    }

    /* cancel modal textarea */
    .sl-textarea {
        width: 100%;
        padding: .5rem .75rem;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-size: .83rem;
        font-family: var(--font);
        color: var(--text);
        resize: vertical;
        min-height: 80px;
        margin-top: .75rem;
        transition: border-color .2s;
    }

    .sl-textarea:focus {
        outline: none;
        border-color: var(--brand-light);
        box-shadow: 0 0 0 3px rgba(177, 135, 82, .15);
    }

    .btn-confirm-cancel {
        padding: .45rem 1.2rem;
        background: var(--danger);
        color: #fff;
        border: none;
        border-radius: 7px;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: background .18s;
    }

    .btn-confirm-cancel:hover {
        background: #b52e2e;
    }

    /* ── LOADING OVERLAY ─────────────────────────────── */
    #ecmrLoadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.75);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
        gap: .75rem;
    }

    #ecmrLoadingOverlay .overlay-msg {
        font-size: .9rem;
        font-weight: 600;
        color: var(--brand);
    }

    /* ── TABLE TOOLBAR ───────────────────────────────── */
    .sl-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .sl-table-search {
        padding: .45rem .75rem;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        font-size: .82rem;
        font-family: var(--font);
        color: var(--text);
        background: var(--surface);
        min-width: 220px;
        transition: border-color .2s;
    }

    .sl-table-search:focus {
        outline: none;
        border-color: var(--brand-light);
        box-shadow: 0 0 0 3px rgba(177, 135, 82, .15);
    }

    .sl-table-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .btn-export,
    .btn-print {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .9rem;
        font-size: .78rem;
        font-weight: 600;
        border-radius: 7px;
        cursor: pointer;
        text-decoration: none;
        transition: background .18s, border-color .18s, color .18s;
        border: 1.5px solid var(--border);
        background: var(--surface);
        color: var(--text);
        font-family: var(--font);
    }

    .btn-export:hover,
    .btn-print:hover {
        background: var(--brand-pale);
        border-color: var(--brand-light);
        color: var(--brand);
    }

    /* ── PAGINATION ──────────────────────────────────── */
    .pagination {
        gap: .25rem;
        flex-wrap: wrap;
    }

    .page-link {
        border: 1.5px solid var(--border);
        border-radius: 6px !important;
        color: var(--text);
        font-size: .8rem;
        font-family: var(--font);
        padding: .35rem .75rem;
        background: var(--surface);
        transition: background .18s, border-color .18s, color .18s;
    }

    .page-link:hover {
        background: var(--brand-pale);
        border-color: var(--brand-light);
        color: var(--brand);
    }

    .page-item.active .page-link {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        box-shadow: none;
    }

    .page-item.disabled .page-link {
        background: var(--surface-2);
        border-color: var(--border);
        color: var(--muted);
    }

    /* ── PRINT ────────────────────────────────────────── */
    @media print {
        .sl-card:has(> .sl-card-header > .fa-shield-halved),
        .sl-card:has(> .sl-card-header > .fa-sliders),
        .sl-table-toolbar,
        .sl-filter-chips,
        .sl-modal-overlay,
        #ecmrLoadingOverlay,
        .btn-view, .btn-cert, .btn-cancel,
        .layout-navbar, .layout-menu,
        footer { display: none !important; }

        #policylist { font-size: .7rem; }
        #policylist thead th,
        #policylist tbody td { padding: .4rem .6rem; }
    }
</style>
@endpush

<x-layouts.app>

    @php $isAdmin = in_array($user->role, ['admin', 'superadmin']); @endphp

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
                    Purchase of Third Party Insurance for Trucks, Lorries and Articulated Vehicles is <strong>not
                        allowed</strong> via this app.
                </div>
                                <div class="sl-notice text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Please be informed that sucessful upload to NIIP/NIID with this app is reliant on
                    your selection of the appropriate insurance type for your vehicle type as per the options below.
                    Incorrect selection may lead to NIIP/NIID upload failure
                    and potential issues with your policy. If you are unsure about which insurance type to select,
                    please consult with your insurance provider or agent before proceeding.
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
                            {{-- FIX #8: Restored to enabled — was incorrectly disabled --}}
                            <p class="text-alert">Occupier's Liability Policy**</p>
                            <button class="btn-tile" name="btnoccupier" disabled>Occupier's Liability</button>
                        </div>

                        <div class="sl-product-tile">
                            {{-- FIX #4: fa-money does not exist — corrected to fa-sack-dollar --}}
                            <span class="tile-icon"><i class="fa-solid fa-sack-dollar"></i></span>
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
                <form action="{{ route('filterreport') }}" method="get" id="filterForm">
                    <div class="sl-filter-grid">

                        {{-- Search --}}
                        <div class="sl-filter-field" style="flex: 2 1 260px;">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search"
                                placeholder="Policy No., Insured Name, Reg No."
                                value="{{ $searchParams['search'] ?? '' }}"
                                style="width:100%;padding:.48rem .75rem;border:1.5px solid var(--border);border-radius:7px;font-size:.83rem;font-family:var(--font);color:var(--text);background:var(--surface);transition:border-color .2s;">
                        </div>

                        {{-- Policy Type --}}
                        <div class="sl-filter-field">
                            <label for="policytype">Policy Type</label>
                            <select name="policytype" id="policytype">
                                <option value="">All Types</option>
                                @forelse ($products as $product)
                                    <option value="{{ $product }}" @selected(($searchParams['policytype'] ?? '') === $product)>{{ ucwords($product) }}</option>
                                @empty
                                    <option value="" disabled>No types available</option>
                                @endforelse
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="sl-filter-field">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                <option value="">All Statuses</option>
                                <option value="approved" @selected(($searchParams['status'] ?? '') === 'approved')>Approved</option>
                                <option value="draft" @selected(($searchParams['status'] ?? '') === 'draft')>Draft</option>
                                <option value="failed" @selected(($searchParams['status'] ?? '') === 'failed')>Failed</option>
                                <option value="cancelled" @selected(($searchParams['status'] ?? '') === 'cancelled')>Cancelled</option>
                            </select>
                        </div>

                        {{-- Date From --}}
                        <div class="sl-filter-field">
                            <label for="datefrom">Date From</label>
                            <input type="date" name="datefrom" id="datefrom"
                                value="{{ $searchParams['datefrom'] ?? '' }}">
                        </div>

                        {{-- Date To --}}
                        <div class="sl-filter-field">
                            <label for="dateto">Date To</label>
                            <input type="date" name="dateto" id="dateto"
                                value="{{ $searchParams['dateto'] ?? '' }}">
                        </div>

                        {{-- Agent (admin only) --}}
                        @if ($isAdmin)
                            <div class="sl-filter-field">
                                <label for="agentcode">Agent</label>
                                <select name="agentcode" id="agentcode">
                                    <option value="">All Agents</option>
                                    @forelse ($agentslist as $agent)
                                        <option value="{{ $agent->uid }}"
                                            @if (($searchParams['agentcode'] ?? '') == $agent->uid) selected @endif>
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
                            <a href="{{ route('list_policy') }}" class="btn-reset">
                                <i class="fa-solid fa-xmark"></i> Clear Filters
                            </a>
                        </div>

                    </div>

                    {{-- Active Filter Chips --}}
                    @if (!empty(array_filter($searchParams)))
                        <div class="sl-filter-chips">
                            <span class="sl-chip-label">Active filters:</span>
                            @if (!empty($searchParams['search']))
                                <span class="sl-chip"><i class="fa-solid fa-magnifying-glass"></i>
                                    "{{ $searchParams['search'] }}"</span>
                            @endif
                            @if (!empty($searchParams['policytype']))
                                <span class="sl-chip"><i class="fa-solid fa-tag"></i>
                                    {{ ucwords($searchParams['policytype']) }}</span>
                            @endif
                            @if (!empty($searchParams['status']))
                                <span class="sl-chip"><i class="fa-solid fa-circle-dot"></i>
                                    {{ ucwords($searchParams['status']) }}</span>
                            @endif
                            @if (!empty($searchParams['datefrom']))
                                <span class="sl-chip"><i class="fa-regular fa-calendar"></i> From
                                    {{ $searchParams['datefrom'] }}</span>
                            @endif
                            @if (!empty($searchParams['dateto']))
                                <span class="sl-chip"><i class="fa-regular fa-calendar"></i> To
                                    {{ $searchParams['dateto'] }}</span>
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
                <div class="sl-table-toolbar">
                    <input type="text" id="tableSearch" class="sl-table-search"
                        placeholder="Quick search this page…" aria-label="Search table rows">
                    <div class="sl-table-actions">
                        <a href="{{ route('policy.export-csv', request()->query()) }}" class="btn-export">
                            <i class="fa-solid fa-file-csv"></i> Export CSV
                        </a>
                        <button type="button" onclick="window.print()" class="btn-print">
                            <i class="fa-solid fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <div class="sl-table-wrap">
                    <table class="table table-sm" id="policylist">
                        <thead>
                            <tr>
                                <th>Policy No.</th>
                                <th>Policy Type</th>
                                <th>Risk / Reg No.</th>
                                <th>Insured Name</th>
                                <th>Contribution</th>
                                <th>Created At</th>
                                <th>Status</th>
                                @if ($isAdmin)
                                    <th>Agent</th>
                                    <th>Parent Agent</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <td colspan="{{ $isAdmin ? 9 : 8 }}">
                                    @if (!empty($searchParams))
                                        <strong>Filtered by:</strong>
                                        @if (!empty($searchParams['policytype']))
                                            Policy Type: <strong>{{ ucwords($searchParams['policytype']) }}</strong>;
                                        @endif
                                        @if (!empty($searchParams['status']))
                                            Status: <strong>{{ ucwords($searchParams['status']) }}</strong>;
                                        @endif
                                        @if (!empty($searchParams['datefrom']))
                                            Date From: <strong>{{ $searchParams['datefrom'] }}</strong>
                                        @endif
                                        @if (!empty($searchParams['dateto']))
                                            To: <strong>{{ $searchParams['dateto'] }}</strong>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        </tfoot>

                        <tbody>
                            @forelse ($policies as $policy)
                                @php $risk = $policy->getrisk(); @endphp
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
                                        @if (empty($risk?->regno))
                                            <span class="policy-incomplete">No Reg No. #{{ $policy->id }}</span>
                                        @else
                                            {{ $risk?->regno }}
                                        @endif
                                    </td>
                                    <td>{{ $policy->insured_name }}</td>
                                    <td>{{ number_format((float)$policy->contribution, 2) }}</td>
                                    <td>{{ $policy->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @switch($policy->status)
                                            @case('approved')
                                                <span class="sl-badge badge-approved"><i class="fa-solid fa-circle-check"></i>
                                                    Approved</span><br>
                                                @if (Str::contains(strtolower($policy->producttype), 'motor'))
                                                    @php $niip = $policy->getniipstatus(); @endphp
                                                    @if (is_array($niip) && ($niip['isSuccess'] ?? false) == true)
                                                        <a href="#" class="niip-trigger"
                                                            data-message="{{ $policy->niip_status }}">
                                                            <span class="sl-badge badge-niip-ok"><i
                                                                    class="fa-solid fa-check"></i> NIIP OK</span>
                                                        </a>
                                                    @elseif (is_array($niip) && ($niip['statusCode'] ?? '') == '11')
                                                        <a href="#" class="niip-trigger"
                                                            data-message="{{ $policy->niip_status }}">
                                                            <span class="sl-badge badge-niip-warn"><i
                                                                    class="fa-solid fa-triangle-exclamation"></i> Possible
                                                                Issue</span>
                                                        </a>
                                                    @else
                                                        <a href="#" class="niip-trigger"
                                                            data-message="{{ $policy->niip_status }}">
                                                            <span class="sl-badge badge-niip-err"><i
                                                                    class="fa-solid fa-circle-xmark"></i> NIIP Issue</span>
                                                        </a>
                                                    @endif
                                                @endif
                                            @break

                                            @case('draft')
                                                <span class="sl-badge badge-draft"><i class="fa-regular fa-clock"></i>
                                                    Draft</span>
                                            @break

                                            @case('failed')
                                                <span class="sl-badge badge-failed"><i class="fa-solid fa-circle-xmark"></i>
                                                    Failed</span>
                                            @break

                                            {{-- FIX #5: Cancelled now uses its own badge-cancelled class --}}
                                            @case('cancelled')
                                                <a href="#" class="niip-trigger"
                                                    data-message="{{ $policy->cancellation_reason ?? 'No reason provided' }}">
                                                    <span class="sl-badge badge-cancelled"><i class="fa-solid fa-ban"></i>
                                                        Cancelled</span>
                                                </a>
                                            @break

                                            @default
                                                <span class="sl-badge badge-default">{{ $policy->status }}</span>
                                        @endswitch
                                        @if (($isAdmin) && str_contains($policy->producttype, 'Motor'))
                                            @php
                                                $emcr = $policy->getemcr();
                                                $message = $emcr?->message ?? 'Emcr Missing';
                                                $response = $emcr?->response ?? 'No EMCR Records Found for this policy';
                                            @endphp

                                            <a href="#" class="niip-trigger"
                                                data-message="{{ $response }}">
                                                <span class="sl-badge badge-emcr">
                                                    <i class="fa-solid fa-shield"></i> EMCR :
                                                    {{ $message }}
                                                </span>
                                            </a>
                                        @endif


                                    </td>

                                    @if ($isAdmin)
                                        <td>{{ $policy->getagentname() }}</td>
                                        <td>{{ $policy->getparentagentname() }}</td>
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
                                        @if (($isAdmin) && str_contains(strtolower($policy->producttype), 'motor'))
                                            <a class="btn-cert ecmr-retry-link"
                                                href="{{ route('ecmr.check', ['ecmr_regno' => $risk?->regno ?? 'n/a']) }}">
                                                <i class="fa-solid fa-shield"></i> Retry ECMR
                                            </a>
                                        @endif

                                        @if ($isAdmin)
                                            {{-- FIX #3: trigger uses custom modal system, not Bootstrap data-bs-toggle --}}
                                            <button class="btn-cancel cancel-trigger"
                                                data-policy-id="{{ $policy->id }}"
                                                data-policy-no="{{ $policy->policyno }}" type="button">
                                                <i class="fa-solid fa-ban"></i> Cancel
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                {{-- FIX #1: Cancel modal is now INSIDE the @forelse loop, one per policy row --}}
                                @if ($isAdmin)
                                    <div class="sl-modal-overlay cancel-modal" id="cancelModal-{{ $policy->id }}">
                                        <div class="sl-modal">
                                            <div class="sl-modal-header danger">
                                                <h5><i class="fa-solid fa-ban"></i> Cancel Policy</h5>
                                                {{-- FIX #2: Removed duplicate id — single close button in header only --}}
                                                <button class="sl-modal-close cancel-modal-close"
                                                    data-target="cancelModal-{{ $policy->id }}" type="button"
                                                    aria-label="Close">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                            <div class="sl-modal-body">
                                                <p>Are you sure you want to cancel policy
                                                    <strong>{{ $policy->policyno ?: '#' . $policy->id }}</strong>? This
                                                    action cannot be undone.
                                                </p>
                                                <form action="{{ route('cancel_policy') }}" method="post">
                                                    @csrf
                                                    <input type="number" value="{{ $policy->id }}" hidden
                                                        name="id">
                                                    <textarea name="cancellation_reason" class="sl-textarea" placeholder="Reason for cancellation (optional)"></textarea>
                                                    <div class="sl-modal-footer">
                                                        <button class="btn-modal-close cancel-modal-close"
                                                            data-target="cancelModal-{{ $policy->id }}"
                                                            type="button">
                                                            <i class="fa-solid fa-arrow-left"></i> Keep Policy
                                                        </button>
                                                        <button class="btn-confirm-cancel" type="submit">
                                                            <i class="fa-solid fa-ban"></i> Yes, Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 10 : 8 }}" style="text-align:center;color:var(--muted);padding:2rem 1rem;">
                                            No policies found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Laravel pagination links — carry filter params through pages --}}
                    <div class="mt-3">
                        {{ $policies->withQueryString()->links() }}
                    </div>

                </div>
            </div>

        </div>{{-- /sl-page --}}

        {{-- ── ECMR LOADING OVERLAY ───────────────────────────────── --}}
        <div id="ecmrLoadingOverlay">
            <div class="spinner-border text-warning" role="status" style="width:3.5rem;height:3.5rem;"></div>
            <span class="overlay-msg"><i class="fa-solid fa-shield"></i> Retrying ECMR, please wait…</span>
        </div>

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

        @push('scripts')
        <script>
            // ── NIIP modal ────────────────────────────────────────────
            const niipModal = document.getElementById('niipModal');
            const niipMsgEl = document.getElementById('niipModalMessage');

            document.querySelectorAll('.niip-trigger').forEach(el => {
                el.addEventListener('click', e => {
                    e.preventDefault();
                    const raw = el.getAttribute('data-message');
                    let display = raw;
                    try {
                        const parsed = JSON.parse(raw);
                        display = JSON.stringify(parsed, null, 2);
                        niipMsgEl.style.cssText = 'white-space:pre-wrap;font-family:var(--mono);font-size:.78rem;';
                    } catch (_) {
                        niipMsgEl.style.cssText = '';
                    }
                    niipMsgEl.textContent = display;
                    niipModal.classList.add('active');
                });
            });
            document.getElementById('niipModalClose').addEventListener('click', () => niipModal.classList.remove('active'));
            document.getElementById('niipModalCloseFooter').addEventListener('click', () => niipModal.classList.remove(
                'active'));
            niipModal.addEventListener('click', e => {
                if (e.target === niipModal) niipModal.classList.remove('active');
            });

            // ── FIX #3: Cancel modal JS — wired to custom overlay system ─
            document.querySelectorAll('.cancel-trigger').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-policy-id');
                    const modal = document.getElementById('cancelModal-' + id);
                    if (modal) modal.classList.add('active');
                });
            });

            document.querySelectorAll('.cancel-modal-close').forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.getAttribute('data-target');
                    const modal = document.getElementById(target);
                    if (modal) modal.classList.remove('active');
                });
            });

            document.querySelectorAll('.cancel-modal').forEach(overlay => {
                overlay.addEventListener('click', e => {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            // ── Retry ECMR spinner ────────────────────────────────────
            const ecmrOverlay = document.getElementById('ecmrLoadingOverlay');
            document.querySelectorAll('.ecmr-retry-link').forEach(link => {
                link.addEventListener('click', () => {
                    ecmrOverlay.style.display = 'flex';
                });
            });

            // ── Client-side row search ────────────────────────────────
            document.getElementById('tableSearch').addEventListener('input', function () {
                const term = this.value.toLowerCase();
                document.querySelectorAll('#policylist tbody tr').forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        </script>
        @endpush

    </x-layouts.app>
