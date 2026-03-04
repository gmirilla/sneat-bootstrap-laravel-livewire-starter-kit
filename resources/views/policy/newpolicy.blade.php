<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<x-layouts.app>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --primary:       #1a56db;
        --primary-dark:  #1342b0;
        --primary-light: #ebf0ff;
        --success:       #0e9f6e;
        --danger:        #e02424;
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

    /* ── Page wrapper ── */
    .policy-wrapper {
        max-width: 900px;
        margin: 0 auto;
        padding: 32px 16px 64px;
    }

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
    .page-header h1 {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -.02em;
        margin: 0 0 2px;
    }
    .page-header p {
        margin: 0;
        font-size: .85rem;
        color: var(--text-muted);
    }

    /* ── Alert ── */
    .alert {
        border-radius: var(--radius-sm);
        padding: 14px 18px;
        margin-bottom: 24px;
        font-size: .9rem;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: var(--danger);
    }
    .alert ul { margin: 6px 0 0 16px; padding: 0; }
    .alert li { margin-bottom: 4px; }

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
    }
    .section-header svg { color: var(--primary); flex-shrink: 0; }
    .section-header h2 {
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--text-label);
        margin: 0;
    }

    .section-body { padding: 22px; }

    /* ── Field grid ── */
    .fields-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 18px;
    }
    .fields-grid.cols-2 {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    }
    .field-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .field-group.full-width { grid-column: 1 / -1; }

    /* ── Labels ── */
    .field-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    /* ── Inputs & selects ── */
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
    .field-input:disabled {
        background: var(--surface-2);
        color: var(--text-muted);
        font-family: 'DM Mono', monospace;
        font-size: .85rem;
        cursor: not-allowed;
    }
    .field-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }
    .field-textarea { resize: vertical; min-height: 88px; }

    /* ── Contribution highlight ── */
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

    /* ── Declaration card ── */
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
        align-items: center;
        gap: 12px;
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
    .btn-submit svg { width: 18px; height: 18px; }

    /* ── Processing overlay ── */
    #processingOverlay {
        position: fixed;
        inset: 0;
        background: rgba(10, 15, 30, 0.75);
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

    /* ── Select2 overrides ── */
    .select2-container--default .select2-selection--single {
        font-family: 'DM Sans', sans-serif;
        font-size: .9rem;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm) !important;
        height: 42px;
        display: flex;
        align-items: center;
        padding: 0 13px;
        transition: border-color var(--transition), box-shadow var(--transition);
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,.12);
        outline: none;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main);
        line-height: normal;
        padding: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    .select2-dropdown {
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm) !important;
        box-shadow: 0 8px 24px rgba(0,0,0,.10);
        font-family: 'DM Sans', sans-serif;
        font-size: .9rem;
    }
    .select2-container--default .select2-results__option--highlighted {
        background-color: var(--primary) !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-family: 'DM Sans', sans-serif;
        padding: 6px 10px;
        outline: none;
    }
    .select2-container { width: 100% !important; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
        .policy-wrapper { padding: 16px 12px 48px; }
        .page-header h1 { font-size: 1.1rem; }
        .section-body { padding: 16px; }
        .fields-grid { grid-template-columns: 1fr 1fr; }
        .fields-grid.cols-2 { grid-template-columns: 1fr; }
    }
    @media (max-width: 420px) {
        .fields-grid { grid-template-columns: 1fr; }
    }
</style>

@php $user = auth()->user(); @endphp

<div class="policy-wrapper">

    {{-- Alerts --}}
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

    {{-- Page header --}}
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </div>
        <div>
            <h1>New Motor Policy</h1>
            <p>Complete all sections below to submit a Motor Third Party policy.</p>
            <p class="text-danger">Please be advised that in line with the NAICOM regulations and the NIIRA all policies must be submitted witha valid NIN or CAC number</p>
        </div>
    </div>

    <form action="{{ route('submit_mpolicy') }}" method="POST" id="policyForm" onsubmit="showOverlay()">
        @csrf

        {{-- Hidden fields --}}
        <input type="hidden" name="producttype"   value="{{ $producttype }}">
        <input type="hidden" name="insurancetype" value="{{ $insurancetype }}">
        <input type="hidden" name="vehicleuse"    value="{{ $vehicleuse }}">
        <input type="hidden" name="niipusecode"   value="{{ $niipusecode }}">
        <input type="hidden" name="contribution"  value="{{ $contribution }}">
        <input type="hidden" name="vehicletype"   value="{{ $usekey }}">

        {{-- ── Product Details ── --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <h2>Product Details</h2>
            </div>
            <div class="section-body">
                <div class="fields-grid">
                    <div class="field-group">
                        <span class="field-label">Product</span>
                        <input class="field-input" type="text" disabled value="Motor Third Party">
                    </div>
                    <div class="field-group">
                        <span class="field-label">Product Type</span>
                        <input class="field-input" type="text" disabled value="{{ $producttype }}">
                    </div>
                    <div class="field-group">
                        <span class="field-label">Contribution (₦)</span>
                        <div class="contribution-value">{{ number_format($contribution, 2) }}</div>
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
                        <input class="field-input" type="text" name="fname" id="fname" required placeholder="First name">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="lname">Last Name</label>
                        <input class="field-input" type="text" name="lname" id="lname" required placeholder="Last name">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="gender">Gender</label>
                        <select class="field-select" name="gender" id="gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="dob">Date of Birth</label>
                        <input class="field-input" type="date" name="dob" id="dob" required min="1925-01-01">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="email">Email Address</label>
                        <input class="field-input" type="email" name="email" id="email" required placeholder="you@example.com">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="phone">Phone Number</label>
                        <input class="field-input" type="tel" name="phone" id="phone" required placeholder="e.g. 08012345678">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="state">State of Residence</label>
                        <select class="field-select" name="state" id="state" required onchange="getlga()">
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->stateid }}">{{ $state->statename }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="lgas">LGA</label>
                        <select class="field-select" name="lgas" id="lgas" required>
                            <option value="">Select LGA</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="nin">NIN / CAC Number</label>
                        <input class="field-input" type="text" name="nin" id="nin" placeholder="NIN or CAC number">
                    </div>

                    <div class="field-group full-width">
                        <label class="field-label" for="address">Address</label>
                        <textarea class="field-textarea" name="address" id="address" required placeholder="Enter full residential address"></textarea>
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
                            value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="regno">Registration No.</label>
                        <input class="field-input" type="text" name="regno" id="regno" required placeholder="e.g. ABC-123-XY">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="chassisno">Chassis No.</label>
                        <input class="field-input" type="text" name="chassisno" id="chassisno" required placeholder="Chassis number">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="engineno">Engine No.</label>
                        <input class="field-input" type="text" name="engineno" id="engineno" required placeholder="Engine number">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="vehiclemake">Vehicle Make</label>
                        <select name="vehiclemake" id="vehiclemake" required class="field-select" onchange="loadModels()">
                            <option value="">Select Make</option>
                            @foreach ($vmakes as $vmake)
                                <option value="{{ $vmake->niipvmid }}">{{ $vmake->vmake }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="vehiclemodel">Vehicle Model</label>
                        <select id="vehiclemodel" name="vmodel" required class="field-select">
                            <option value="">Select Model</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="yearofmake">Year of Make</label>
                        <input class="field-input" type="number" name="yearofmake" id="yearofmake" required
                            placeholder="e.g. 2019" min="1970" max="{{ date('Y') }}">
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="colors">Vehicle Colour</label>
                        <select name="vehiclecolor" id="colors" class="field-select" required>
                            <option value="">Select Colour</option>
                            @foreach ($colors as $color)
                                <option value="{{ $color->colorid }}">{{ $color->color }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Declaration ── --}}
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

                @if (in_array($user->role, ['agent', 'subagent', 'user']))
                    <div class="submit-row">
                        <button class="btn-submit" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                            Submit Policy
                        </button>
                    </div>
                @endif

            </div>
        </div>

    </form>
</div>

{{-- Processing overlay --}}
<div id="processingOverlay" role="status" aria-live="polite">
    <div class="overlay-spinner"></div>
    <div class="overlay-text">Submitting policy&hellip;</div>
</div>

<script>
    // DOB max: must be at least 18 years old
    (function () {
        const dob = document.getElementById('dob');
        const today = new Date();
        const y = today.getFullYear() - 18;
        const m = String(today.getMonth() + 1).padStart(2, '0');
        const d = String(today.getDate()).padStart(2, '0');
        dob.max = `${y}-${m}-${d}`;
    })();

    // Load vehicle models via AJAX
    function loadModels() {
        const makeId = document.getElementById('vehiclemake').value;
        if (!makeId) return;
        $.ajax({
            url: '/get-vehicle-models/' + makeId,
            type: 'GET',
            success: function (models) {
                const $sel = $('#vehiclemodel').empty().append('<option value="">Select Model</option>');
                models.forEach(function (model) {
                    $sel.append('<option value="' + model.vmodelid + '">' + model.vmodelname + '</option>');
                });
            }
        });
    }

    // Load LGAs via AJAX
    function getlga() {
        const stateId = document.getElementById('state').value;
        if (!stateId) return;
        $.ajax({
            url: '/get-lga/' + stateId,
            type: 'GET',
            success: function (lgas) {
                const $sel = $('#lgas').empty().append('<option value="">Select LGA</option>');
                lgas.forEach(function (lga) {
                    $sel.append('<option value="' + lga.lgaid + '">' + lga.lganame + '</option>');
                });
            }
        });
    }

    // Show overlay on submit
    function showOverlay() {
        document.getElementById('processingOverlay').classList.add('active');
    }
</script>

</x-layouts.app>