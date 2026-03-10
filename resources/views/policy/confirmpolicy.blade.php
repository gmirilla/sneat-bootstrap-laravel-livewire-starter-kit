@php
    use App\Models\agentsdetailsModel;
    Auth::check();
    $usercheck = Auth::user();

    $agent = agentsdetailsModel::where('uid', $usercheck->id)->first();

    if($usercheck->role == 'agent') {
    $creditleft = $agent->noallocated - $agent->noused;
    }

    if ($usercheck->role == 'subagent') {
        $creditleft = $agent->subcreditassigned - $agent->subcreditused;
    }
@endphp

<x-layouts.app>

<script src="https://js.paystack.co/v2/inline.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --primary:       #1a56db;
        --primary-dark:  #1342b0;
        --primary-light: #ebf0ff;
        --success:       #0e9f6e;
        --danger:        #e02424;
        --warning:       #ff8800;
        --surface:       #ffffff;
        --surface-2:     #f8f9fc;
        --border:        #e5e9f2;
        --text-main:     #111827;
        --text-muted:    #6b7280;
        --text-label:    #374151;
        --radius:        12px;
        --radius-sm:     8px;
        --shadow-sm:     0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md:     0 4px 16px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
        --shadow-lg:     0 12px 40px rgba(0,0,0,.10);
        --transition:    0.2s cubic-bezier(.4,0,.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
        min-height: 100vh;
    }

    /* ── Page wrapper ── */
    .policy-wrapper {
        max-width: 860px;
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
        color: var(--text-main);
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
    .section-card:nth-child(2) { animation-delay: .05s; }
    .section-card:nth-child(3) { animation-delay: .10s; }
    .section-card:nth-child(4) { animation-delay: .15s; }
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
    }
    .section-header svg {
        color: var(--primary);
        flex-shrink: 0;
    }
    .section-header h2 {
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--text-label);
        margin: 0;
    }

    .section-body {
        padding: 22px;
    }

    /* ── Field grid ── */
    .fields-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
    }
    .fields-grid.wide {
        grid-template-columns: 1fr;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .field-label {
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
    }
    .field-value {
        font-family: 'DM Mono', monospace;
        font-size: .88rem;
        font-weight: 500;
        color: var(--text-main);
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 13px;
        line-height: 1.4;
    }
    /* Hidden inputs */
    input[type="hidden"] { display: none !important; }

    /* ── Badges ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .badge-primary {
        background: var(--primary-light);
        color: var(--primary);
    }

    /* ── Payment section ── */
    .payment-section {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .credit-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, #ebf5ff 0%, #e0edff 100%);
        border: 1px solid #c3d9ff;
        border-radius: var(--radius-sm);
        padding: 14px 18px;
    }
    .credit-banner svg { color: var(--primary); flex-shrink: 0; }
    .credit-banner-text { font-size: .88rem; color: var(--text-label); }
    .credit-banner-text strong { color: var(--primary); }

    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .pay-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 20px 16px;
        border-radius: var(--radius-sm);
        border: 2px solid var(--border);
        background: var(--surface);
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        font-size: .9rem;
        color: var(--text-main);
        transition: all var(--transition);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .pay-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity var(--transition);
    }
    .pay-btn:hover:not(:disabled) {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,.12);
        transform: translateY(-2px);
    }
    .pay-btn:hover:not(:disabled)::before { opacity: 1; }
    .pay-btn:active:not(:disabled) { transform: translateY(0); }

    .pay-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }
    .pay-btn svg {
        width: 28px; height: 28px;
    }
    .pay-btn-label { font-size: .78rem; color: var(--text-muted); font-weight: 400; }

    .pay-btn-paystack { border-color: #00c3f7; }
    .pay-btn-paystack svg { color: #00c3f7; }
    .pay-btn-paystack:hover:not(:disabled) {
        border-color: #00c3f7;
        box-shadow: 0 0 0 3px rgba(0,195,247,.15);
    }

    .pay-btn-credit { border-color: var(--primary); }
    .pay-btn-credit svg { color: var(--primary); }
    .pay-btn-credit:hover:not(:disabled) {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,.15);
    }

    .pay-btn-moniepoint { border-color: #e5e9f2; }

    .coming-soon-tag {
        position: absolute;
        top: 8px; right: -18px;
        background: var(--warning);
        color: #fff;
        font-size: .6rem;
        font-weight: 700;
        padding: 2px 22px;
        transform: rotate(30deg);
        letter-spacing: .06em;
    }

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

    /* ── Responsive ── */
    @media (max-width: 600px) {
        .policy-wrapper { padding: 16px 12px 48px; }
        .page-header { gap: 10px; margin-bottom: 20px; }
        .page-header h1 { font-size: 1.1rem; }
        .section-body { padding: 16px; }
        .fields-grid { grid-template-columns: 1fr 1fr; }
        .payment-methods { grid-template-columns: 1fr; }
    }
    @media (max-width: 380px) {
        .fields-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="policy-wrapper">

    {{-- Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" flex-shrink="0" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
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
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div>
            <h1>Confirm Policy Details</h1>
            <p>Review all information carefully before completing payment.</p>
        </div>
    </div>

    {{-- Main form --}}
    <form id="paymentForm" action="{{ route('pay_policy_old') }}" method="POST">
        @csrf
        <input type="hidden" name="policyid" value="{{ $policy->id }}">

        {{-- Product Details --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <h2>Product Details</h2>
            </div>
            <div class="section-body">
                <div class="fields-grid">
                    <div class="field-group">
                        <span class="field-label">Product</span>
                        <div class="field-value">Motor Third Party</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Product Type</span>
                        <div class="field-value">{{ $policy->producttype }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Date From</span>
                        <div class="field-value">{{ \Carbon\Carbon::parse($policy->start_date)->format('d M Y') }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Date To</span>
                        <div class="field-value">{{ \Carbon\Carbon::parse($policy->end_date)->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Personal Details --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <h2>Personal Details</h2>
            </div>
            <div class="section-body">
                <div class="fields-grid wide">
                    <div class="field-group">
                        <span class="field-label">Name on Certificate</span>
                        <div class="field-value">{{ $policy->insured_name }}</div>
                    </div>

                    <div class="field-group">
                        <span class="field-label">NIN /CAC Number</span>
                        <div class="field-value">{{ $policy->nin ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicle Details --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                <h2>Vehicle Details</h2>
            </div>
            <div class="section-body">
                <div class="fields-grid">
                    <div class="field-group">
                        <span class="field-label">Registration No.</span>
                        <div class="field-value">{{ $policyrisk->regno }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Chassis No.</span>
                        <div class="field-value">{{ $policyrisk->chassisno }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Engine No.</span>
                        <div class="field-value">{{ $policyrisk->engineno }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Vehicle Make</span>
                        <div class="field-value">{{ $policyrisk->vehiclemake }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Vehicle Model</span>
                        <div class="field-value">{{ $policyrisk->vehiclemodel }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Year of Make</span>
                        <div class="field-value">{{ $policyrisk->yearofmake }}</div>
                    </div>
                    <div class="field-group">
                        <span class="field-label">Vehicle Colour</span>
                        <div class="field-value">{{ $policyrisk->vehiclecolor }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Method --}}
        <div class="section-card">
            <div class="section-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                <h2>Select Payment Method</h2>
            </div>
            <div class="section-body">
                <div class="payment-section">

                    {{-- Credit banner --}}
                    @if (in_array($usercheck->role, ['agent', 'subagent', 'user']) && $agent->allowcredit == true && $creditleft > 0)
                        <div class="credit-banner">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                            <div class="credit-banner-text">
                                Agency credit available &mdash; you have <strong>{{ $creditleft }} upload credit{{ $creditleft == 1 ? '' : 's' }}</strong> remaining.
                            </div>
                        </div>
                    @endif

                    {{-- Payment method buttons --}}
                    <div class="payment-methods">

                        {{-- Paystack --}}
                        @if ($accesscode != null)
                        <button
                            type="button"
                            class="pay-btn pay-btn-paystack"
                            onclick="paywithpaystack(event)"
                            data-acode="{{ $accesscode }}"
                            title="Pay with Paystack">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20" stroke-width="2"/></svg>
                            <span>Paystack</span>
                            <span class="pay-btn-label">Card / Bank Transfer</span>
                        </button>
                        @endif

                        {{-- Moniepoint (coming soon) --}}
                        <button
                            type="button"
                            class="pay-btn pay-btn-moniepoint"
                            disabled
                            title="Coming soon">
                            <span class="coming-soon-tag">Soon</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
                            <span>Moniepoint</span>
                            <span class="pay-btn-label">Coming soon</span>
                        </button>

                        {{-- Agency Credit --}}
                        @if (in_array($usercheck->role, ['agent', 'subagent', 'user']) && $agent->allowcredit == true && $creditleft > 0)
                        <button
                            type="button"
                            class="pay-btn pay-btn-credit"
                            onclick="showProcessingAndSubmit(this)"
                            id="acreditbtn"
                            title="Use agency credit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                            <span>Agency Credit</span>
                            <span class="pay-btn-label">Use upload credits</span>
                        </button>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- Processing overlay --}}
<div id="processingOverlay" role="status" aria-live="polite">
    <div class="overlay-spinner"></div>
    <div class="overlay-text">Processing payment&hellip;</div>
</div>

<script>
    function showProcessingAndSubmit(btn) {
        document.getElementById('processingOverlay').classList.add('active');
        btn.disabled = true;

        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'agencycredit';
        input.value = '1';
        btn.form.appendChild(input);

        btn.form.submit();
    }

    function paywithpaystack(event) {
        event.preventDefault();

        const access_code = event.currentTarget.getAttribute('data-acode');
        const popup = new PaystackPop();

        popup.resumeTransaction(access_code, {
            onCancel: () => {
                console.log('User cancelled Paystack');
            },
            onSuccess: (transaction) => {
                const form = document.getElementById('paymentForm');

                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'paystack';
                input.value = JSON.stringify(transaction);
                form.appendChild(input);

                document.getElementById('processingOverlay').classList.add('active');
                form.submit();
            },
            onError: (error) => {
                console.error('Paystack error:', error.message);
                alert('Payment failed: ' + error.message);
            }
        });
    }
</script>

</x-layouts.app>