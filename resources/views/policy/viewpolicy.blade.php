<x-layouts.app>

@php
    $editable = in_array($policy->status, ['draft', 'failed']);
    $statcheck = $editable ? '' : 'disabled';
    $user = auth()->user();

    // Resolve state name
    $statename = '';
    foreach ($states as $state) {
        if ($state->stateid == $policy->stateid) {
            $statename = $state->statename;
            break;
        }
    }
@endphp

@push('styles')
<style>
    :root {
        --primary:       #1a56db;
        --primary-dark:  #1342b0;
        --primary-light: #ebf0ff;
        --success:       #0e9f6e;
        --success-light: #ecfdf5;
        --danger:        #e02424;
        --danger-light:  #fef2f2;
        --warning:       #d97706;
        --warning-light: #fffbeb;
        --info:          #0284c7;
        --info-light:    #e0f2fe;
        --surface:       #ffffff;
        --surface-2:     #f8f9fc;
        --border:        #e5e9f2;
        --text-main:     #111827;
        --text-muted:    #6b7280;
        --text-label:    #374151;
        --radius:        12px;
        --radius-sm:     8px;
        --shadow-sm:     0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --transition:    0.2s cubic-bezier(.4,0,.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
    }

    .policy-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding: 32px 16px 64px;
    }

    /* ── Alerts ── */
    .alert {
        border-radius: var(--radius-sm);
        padding: 14px 18px;
        margin-bottom: 16px;
        font-size: .9rem;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .alert-danger  { background: var(--danger-light);  border: 1px solid #fecaca; color: var(--danger); }
    .alert-info    { background: var(--info-light);    border: 1px solid #bae6fd; color: var(--info); }
    .alert ul { margin: 6px 0 0 16px; padding: 0; }
    .alert li { margin-bottom: 4px; }

    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 2px solid var(--border);
    }
    .page-header-icon {
        width: 48px; height: 48px;
        background: var(--primary);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .page-header-icon svg { color: #fff; }
    .page-header-text h1 {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -.02em;
        margin: 0 0 4px;
    }
    .page-header-text p { margin: 0; font-size: .85rem; color: var(--text-muted); }

    /* ── Status badge ── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 999px;
    }
    .status-badge-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .status-approved  { background: var(--success-light); color: var(--success); }
    .status-approved  .status-badge-dot { background: var(--success); }
    .status-draft     { background: var(--info-light);    color: var(--info);    }
    .status-draft     .status-badge-dot { background: var(--info); }
    .status-failed    { background: var(--danger-light);  color: var(--danger);  }
    .status-failed    .status-badge-dot { background: var(--danger); }
    .status-default   { background: var(--surface-2);     color: var(--text-muted); }
    .status-default   .status-badge-dot { background: var(--text-muted); }

    /* ── Section card ── */
    .section-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        overflow: hidden;
        animation: slideUp .3s ease both;
    }
    .section-card:nth-child(1) { animation-delay: .04s; }
    .section-card:nth-child(2) { animation-delay: .08s; }
    .section-card:nth-child(3) { animation-delay: .12s; }
    .section-card:nth-child(4) { animation-delay: .16s; }
    .section-card:nth-child(5) { animation-delay: .20s; }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 22px;
        background: var(--surface-2);
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
        row-gap: 8px;
    }
    .section-header svg { color: var(--primary); flex-shrink: 0; }
    .section-header h2 {
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--text-label);
        margin: 0;
        flex: 1;
    }
    .section-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .section-body { padding: 22px; }

    /* ── Policy meta bar ── */
    .policy-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding: 16px 22px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }
    .policy-no {
        font-family: 'DM Mono', monospace;
        font-size: .88rem;
        font-weight: 500;
        color: var(--primary);
        background: var(--primary-light);
        border: 1px solid #c3d9ff;
        border-radius: var(--radius-sm);
        padding: 6px 12px;
    }
    .policy-no-placeholder {
        font-size: .82rem;
        color: var(--text-muted);
        font-style: italic;
    }

    /* ── Collapsible NIIP response ── */
    .niip-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .8rem;
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-light);
        border: 1px solid #c3d9ff;
        border-radius: var(--radius-sm);
        padding: 6px 12px;
        cursor: pointer;
        text-decoration: none;
        transition: background var(--transition);
    }
    .niip-toggle:hover { background: #dde9ff; }
    .niip-panel {
        display: none;
        margin: 0 22px 16px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        font-size: .875rem;
        line-height: 1.6;
        color: var(--text-label);
        font-family: 'DM Mono', monospace;
    }
    .niip-panel.open { display: block; }

    /* ── Retry NIIP card ── */
    .niip-card {
        background: var(--warning-light);
        border: 1px solid #fcd34d;
        border-radius: var(--radius-sm);
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 20px;
    }
    .niip-card svg { color: var(--warning); flex-shrink: 0; margin-top: 2px; }
    .niip-card-text { flex: 1; }
    .niip-card-text strong { display: block; font-size: .88rem; margin-bottom: 4px; color: var(--warning); }
    .niip-card-text p { margin: 0 0 12px; font-size: .84rem; color: var(--text-label); line-height: 1.5; word-break: break-word; }

    /* ── Cert button ── */
    .btn-cert {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--success);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: .8rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        transition: opacity var(--transition);
    }
    .btn-cert:hover { opacity: .88; color: #fff; }

    .btn-retry {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--warning);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: .85rem;
        font-weight: 600;
        padding: 9px 18px;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: opacity var(--transition);
    }
    .btn-retry:hover { opacity: .88; }

    /* ── Fields ── */
    .fields-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 18px;
    }
    .field-group { display: flex; flex-direction: column; gap: 5px; }
    .field-group.full-width { grid-column: 1 / -1; }

    .field-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .field-input,
    .field-select,
    .field-textarea {
        font-family: 'DM Sans', sans-serif;
        font-size: .9rem;
        color: var(--text-main);
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 13px;
        width: 100%;
        transition: border-color var(--transition), box-shadow var(--transition);
        appearance: none;
        -webkit-appearance: none;
        outline: none;
    }
    .field-input::placeholder,
    .field-textarea::placeholder { color: #9ca3af; }
    .field-input:focus,
    .field-select:focus,
    .field-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,.12);
    }
    .field-input[disabled],
    .field-select[disabled],
    .field-textarea[disabled] {
        background: var(--surface-2);
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
        font-size: .85rem;
        cursor: not-allowed;
        opacity: 1;
    }
    .field-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }
    .field-textarea { resize: vertical; min-height: 88px; }

    .contribution-value {
        font-family: 'DM Mono', monospace;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-light);
        border: 1.5px solid #c3d9ff;
        border-radius: var(--radius-sm);
        padding: 10px 13px;
    }

    /* ── Declaration ── */
    .declaration-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .declaration-check {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 16px;
        cursor: pointer;
        transition: border-color var(--transition);
    }
    .declaration-check:has(input:checked) {
        border-color: var(--primary);
        background: var(--primary-light);
    }
    .declaration-check input[type="checkbox"] {
        width: 18px; height: 18px;
        accent-color: var(--primary);
        flex-shrink: 0;
        margin-top: 2px;
        cursor: pointer;
    }
    .declaration-check label {
        font-size: .88rem;
        line-height: 1.6;
        color: var(--text-label);
        cursor: pointer;
    }

    /* ── Submit row ── */
    .submit-row {
        display: flex;
        justify-content: flex-end;
        padding-top: 4px;
    }
    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: .92rem;
        font-weight: 600;
        padding: 11px 28px;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
        box-shadow: 0 2px 8px rgba(26,86,219,.25);
    }
    .btn-submit:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(26,86,219,.30);
    }
    .btn-submit:active { transform: translateY(0); }

    /* ── Processing overlay ── */
    #processingOverlay {
        position: fixed;
        inset: 0;
        background: rgba(10,15,30,.75);
        backdrop-filter: blur(4px);
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        gap: 16px;
    }
    #processingOverlay.active { display: flex; }
    .overlay-spinner {
        width: 48px; height: 48px;
        border: 3px solid rgba(255,255,255,.2);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .overlay-text {
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: .03em;
    }

    /* ── Responsive ── */
    @media (max-width: 640px) {
        .policy-wrapper { padding: 16px 12px 48px; }
        .page-header-text h1 { font-size: 1.1rem; }
        .section-body { padding: 16px; }
        .fields-grid { grid-template-columns: 1fr 1fr; }
        .policy-meta { gap: 8px; }
    }
    @media (max-width: 420px) {
        .fields-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

<div class="policy-wrapper">

    {{-- ── Alerts ── --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!empty($retrymessage))
        <div class="alert alert-info" role="status">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            <span>{{ $retrymessage }}</span>
        </div>
    @endif

    {{-- ── NIIP Retry (admin only) ── --}}
    @if ($policy->status == 'approved' && in_array($user->role, ['admin', 'superadmin']))
        <div class="niip-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            <div class="niip-card-text">
                <strong>NIIP Submission Required</strong>
                <p>{{ $policy->niip_status }}</p>
                <form action="{{ route('retry_niip') }}" method="GET" style="display:inline">
                    <input type="hidden" name="policyno" value="{{ $policy->policyno }}">
                    <button type="submit" class="btn-retry">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Retry NIIP Submission
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ── Page header ── --}}
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div class="page-header-text">
            <h1>Motor Policy</h1>
            <p>{{ $editable ? 'Review and update policy details below.' : 'Policy record — read only.' }}</p>
        </div>
    </div>

    {{-- ── Main form ── --}}
    <form action="{{ route('submit_mpolicy') }}" method="POST" id="policyForm" onsubmit="showOverlay()">
        @csrf
        <input type="hidden" name="producttype"  value="{{ $policy->producttype }}">
        <input type="hidden" name="policyid"     value="{{ $policy->id }}">
        <input type="hidden" name="contribution" value="{{ $policy->contribution }}">
        <input type="hidden" name="vehicletype"  value="{{ $policy->usekey }}">
        <input type="hidden" name="niipusecode"  value="{{ $policy->niipvehicleuse }}">

        {{-- ── Policy Details card ── --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <h2>Policy Details</h2>
                <div class="section-header-actions">
                    {{-- Status badge --}}
                    @php
                        $statusClass = match($policy->status) {
                            'approved' => 'status-approved',
                            'draft'    => 'status-draft',
                            'failed'   => 'status-failed',
                            default    => 'status-default',
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        <span class="status-badge-dot"></span>
                        {{ strtoupper($policy->status) }}
                    </span>

                    {{-- NIIP response toggle --}}
                    <button type="button" class="niip-toggle" onclick="toggleNiip()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        View Log
                    </button>

                    {{-- Certificate link --}}
                    @if ($policy->status == 'approved')
                        <a class="btn-cert" href="http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no={{ $policy->policyno }}" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            Certificate
                        </a>
                    @endif
                </div>
            </div>

            {{-- Policy number bar --}}
            <div class="policy-meta">
                <span style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted)">Policy No.</span>
                @if (!empty($policy->policyno))
                    <span class="policy-no">{{ $policy->policyno }}</span>
                @else
                    <span class="policy-no-placeholder">Not yet generated</span>
                @endif
            </div>

            {{-- NIIP log panel --}}
            <div class="niip-panel" id="niipPanel">
                {{ !empty($policy->elite_msg) ? $policy->elite_msg : 'No log available.' }}
            </div>

            <div class="section-body">
                <div class="fields-grid">
                    <div class="field-group">
                        <span class="field-label">Product</span>
                        <input class="field-input" type="text" disabled value="Motor Third Party">
                    </div>
                    <div class="field-group">
                        <span class="field-label">Product Type</span>
                        <input class="field-input" type="text" disabled value="{{ $policy->producttype }}">
                    </div>
                    <div class="field-group">
                        <span class="field-label">Contribution (₦)</span>
                        <div class="contribution-value">{{ number_format($policy->contribution, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Personal Details ── --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <h2>Personal Details</h2>
            </div>
            <div class="section-body">
                <div class="fields-grid">

                    <div class="field-group">
                        <label class="field-label" for="fname">First Name</label>
                        <input class="field-input" type="text" name="fname" id="fname"
                            {{ $statcheck }} required value="{{ $insured->firstname }}" placeholder="First name">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="lname">Last Name</label>
                        <input class="field-input" type="text" name="lname" id="lname"
                            {{ $statcheck }} required value="{{ $insured->lastname }}" placeholder="Last name">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="gender">Gender</label>
                        <select class="field-select" name="gender" id="gender" {{ $statcheck }}>
                            <option value="Male" @selected($insured->gender === 'Male')>Male</option>
                            <option value="Female" @selected($insured->gender === 'Female')>Female</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="dob">Date of Birth</label>
                        <input class="field-input" type="date" name="dob" id="dob"
                            {{ $statcheck }} required value="{{ $insured->dob }}">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="email">Email Address</label>
                        <input class="field-input" type="email" name="email" id="email"
                            {{ $statcheck }} value="{{ $insured->email }}" placeholder="you@example.com">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="phone">Phone Number</label>
                        <input class="field-input" type="tel" name="phone" id="phone"
                            {{ $statcheck }} required value="{{ $insured->telno }}" placeholder="e.g. 08012345678">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="state">State of Residence</label>
                        @if (!$editable)
                            <input class="field-input" type="text" disabled value="{{ $statename }}">
                        @else
                            <select class="field-select" name="state" id="state" required onchange="getlga()">
                                <option value="">Select State</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->stateid }}"
                                        {{ $policy->stateid == $state->stateid ? 'selected' : '' }}>
                                        {{ $state->statename }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="lgas">LGA</label>
                        @if (!$editable)
                            <input class="field-input" type="text" disabled value="{{ $policy->getlga() }}">
                        @else
                            <select class="field-select" name="lgas" id="lgas" required>
                                <option value="">Select LGA</option>
                            </select>
                        @endif
                    </div>

                    <div class="field-group full-width">
                        <label class="field-label" for="address">Address</label>
                        <textarea class="field-textarea" name="address" id="address"
                            {{ $statcheck }} required placeholder="Enter address">{{ $insured->address }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Vehicle Details ── --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                <h2>Vehicle Details</h2>
            </div>
            <div class="section-body">
                <div class="fields-grid">
                                        <div class="field-group">
                        <label class="field-label" for="start_date">Policy Start Date</label>
                        <input class="field-input" type="date" name="start_date" id="start_date" required
                            value="{{ $policy->start_date ? date('Y-m-d', strtotime($policy->start_date)) : date('Y-m-d') }}"
                            min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="regno">Registration No.</label>
                        <input class="field-input" type="text" name="regno" id="regno"
                            {{ $statcheck }} required value="{{ $policyrisk->regno }}" placeholder="e.g. ABC-123-XY">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="chassisno">Chassis No.</label>
                        <input class="field-input" type="text" name="chassisno" id="chassisno"
                            {{ $statcheck }} required value="{{ $policyrisk->chassisno }}" placeholder="Chassis number">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="engineno">Engine No.</label>
                        <input class="field-input" type="text" name="engineno" id="engineno"
                            {{ $statcheck }} required value="{{ $policyrisk->engineno }}" placeholder="Engine number">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="vehiclemake">Vehicle Make</label>
                        @if (!$editable)
                            <input class="field-input" type="text" disabled value="{{ $policyrisk->vehiclemake }}">
                        @else
                            <select name="vehiclemake" id="vehiclemake" required class="field-select" onchange="loadModels()">
                                <option value="">Select Make</option>
                                @foreach ($vmakes as $vmake)
                                    <option value="{{ $vmake->niipvmid }}"
                                        {{ $policyrisk->vehiclemake == $vmake->vmake ? 'selected' : '' }}>
                                        {{ $vmake->vmake }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="vehiclemodel">Vehicle Model</label>
                        @if (!$editable)
                            <input class="field-input" type="text" name="vehiclemodel" disabled value="{{ $policyrisk->vehiclemodel }}">
                        @else
                            <select id="vehiclemodel" name="vmodel" required class="field-select">
                                <option value="">Select Model</option>
                            </select>
                        @endif
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="yearofmake">Year of Make</label>
                        <input class="field-input" type="number" name="yearofmake" id="yearofmake"
                            {{ $statcheck }} required value="{{ $policyrisk->yearofmake }}"
                            min="1970" max="{{ date('Y') }}" placeholder="e.g. 2019">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="colors">Vehicle Colour</label>
                        <select name="vehiclecolor" id="colors" {{ $statcheck }} class="field-select" required>
                            <option value="">Select Colour</option>
                            @foreach ($colors as $color)
                                <option value="{{ $color->colorid }}" @selected($color->colorid == $policyrisk->vechiclecolorid)>{{ $color->color }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Declaration (only when editable) ── --}}
        @if ($editable)
            <div class="section-card">
                <div class="section-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    <h2>Declaration</h2>
                </div>
                <div class="declaration-body">
                    <label class="declaration-check">
                        <input type="checkbox" name="declaration" id="declaration" required>
                        <span>I declare that I have read the privacy information on the use of personal data and confirm that the information above is correct to the best of my knowledge. I also consent to the processing of my personal data in accordance with the Company's Privacy Policy.</span>
                    </label>
                    <div class="submit-row">
                        <button class="btn-submit" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                            Submit Policy
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </form>
</div>

{{-- Processing overlay --}}
<div id="processingOverlay" role="status" aria-live="polite">
    <div class="overlay-spinner"></div>
    <div class="overlay-text">Submitting policy&hellip;</div>
</div>

@push('scripts')
<script>
    function toggleNiip() {
        document.getElementById('niipPanel').classList.toggle('open');
    }

    function showOverlay() {
        document.getElementById('processingOverlay').classList.add('active');
    }

    function loadModels(makeId, preselectName) {
        const id = makeId ?? document.getElementById('vehiclemake')?.value;
        if (!id) return;
        fetch('/get-vehicle-models/' + id)
            .then(r => r.json())
            .then(models => {
                const sel = document.getElementById('vehiclemodel');
                sel.innerHTML = '<option value="">Select Model</option>';
                models.forEach(m => {
                    const opt = new Option(m.vmodelname, m.vmodelid);
                    if (preselectName && m.vmodelname === preselectName) opt.selected = true;
                    sel.appendChild(opt);
                });
            });
    }

    function getlga(stateId, preselectLgaId) {
        const id = stateId ?? document.getElementById('state')?.value;
        if (!id) return;
        fetch('/get-lga/' + id)
            .then(r => r.json())
            .then(lgas => {
                const sel = document.getElementById('lgas');
                sel.innerHTML = '<option value="">Select LGA</option>';
                lgas.forEach(l => {
                    const opt = new Option(l.lganame, l.lgaid);
                    if (preselectLgaId && String(l.lgaid) === String(preselectLgaId)) opt.selected = true;
                    sel.appendChild(opt);
                });
            });
    }

    @if ($editable)
    // Pre-populate dependent dropdowns from saved policy values
    const _makeEl = document.getElementById('vehiclemake');
    if (_makeEl?.value) loadModels(_makeEl.value, @json($policyrisk->vehiclemodel ?? ''));
    if (@json($policy->stateid)) getlga(@json($policy->stateid), @json($policy->lgaid));
    @endif
</script>
@endpush

</x-layouts.app>