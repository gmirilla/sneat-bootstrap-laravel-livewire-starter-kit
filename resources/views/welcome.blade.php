<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@section('title', __('Welcome'))
<head>
    @include('partials.head')
    {{-- Custom CSS already provides DM Sans + brand tokens --}}
    <link rel="stylesheet" href="{{ asset('css/mysalamcustom.css') }}">
    <style>
        html { scroll-behavior: smooth; }

        /* ── Welcome-page scoped styles ── */
        .wlc-navbar {
            background: #161616;
            position: sticky;
            top: 0;
            z-index: 1030;
            padding: .75rem 0;
            box-shadow: 0 2px 12px rgba(0,0,0,.35);
        }
        .wlc-navbar .navbar-brand-text {
            color: #B18752;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -.01em;
        }
        .wlc-btn-gold {
            background: #B18752;
            color: #161616 !important;
            font-weight: 700;
            border: none;
            transition: filter .15s;
        }
        .wlc-btn-gold:hover { filter: brightness(1.12); }
        .wlc-btn-outline {
            border: 1.5px solid #B18752;
            color: #B18752 !important;
            background: transparent;
            font-weight: 600;
            transition: background .15s, color .15s;
        }
        .wlc-btn-outline:hover { background: #B18752; color: #161616 !important; }

        /* ── Hero ── */
        .wlc-hero {
            background: #161616;
            padding: 88px 0 72px;
            overflow: hidden;
            position: relative;
        }
        .wlc-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 75% 50%, rgba(177,135,82,.15) 0%, transparent 65%);
            pointer-events: none;
        }
        .wlc-hero-eyebrow {
            color: #B18752;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: .75rem;
        }
        .wlc-hero h1 {
            color: #fff;
            font-size: clamp(2rem, 5vw, 3.1rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -.025em;
            margin-bottom: 1.25rem;
        }
        .wlc-hero h1 span { color: #B18752; }
        .wlc-hero-sub {
            color: rgba(255,255,255,.68);
            font-size: 1.05rem;
            line-height: 1.75;
            max-width: 440px;
            margin-bottom: 2.25rem;
        }
        .wlc-hero-img {
            max-height: 380px;
            object-fit: contain;
            filter: drop-shadow(0 24px 48px rgba(0,0,0,.5));
        }

        /* ── Feature cards ── */
        .wlc-features { padding: 72px 0; background: #f8f9fc; }
        .wlc-feature-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0,0,0,.06);
            transition: transform .2s, box-shadow .2s;
            height: 100%;
        }
        .wlc-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 36px rgba(0,0,0,.11);
        }
        .wlc-feature-card.dark { background: #161616; }
        .wlc-icon-wrap {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.25rem;
            flex-shrink: 0;
        }
        .wlc-icon-wrap i { font-size: 1.65rem; }

        /* ── Self-service tools ── */
        .wlc-tools { padding: 72px 0; background: #fff; }
        .wlc-tool-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
            height: 100%;
        }
        .wlc-tool-card .form-control {
            border-color: #e5e9f2;
            border-radius: 10px 0 0 10px;
            padding: .65rem 1rem;
            font-size: .95rem;
        }
        .wlc-tool-card .form-control:focus {
            border-color: #B18752;
            box-shadow: 0 0 0 3px rgba(177,135,82,.15);
        }
        .wlc-tool-btn {
            border-radius: 0 10px 10px 0;
            padding: .65rem 1.35rem;
            font-weight: 700;
            font-size: .9rem;
        }

        /* ── Section heading ── */
        .wlc-section-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #161616;
            letter-spacing: -.02em;
            margin-bottom: .5rem;
        }
        .wlc-section-sub {
            color: #6b7280;
            max-width: 460px;
            margin: 0 auto;
        }

        /* ── Footer ── */
        .wlc-footer {
            background: #161616;
            padding: 48px 0 24px;
        }
        .wlc-footer a { transition: color .15s; }
        .wlc-footer a:hover { color: #B18752 !important; }
        .wlc-footer-divider {
            border-top: 1px solid rgba(255,255,255,.1);
            margin: 28px 0 20px;
        }
    </style>
</head>

<body style="margin:0;padding:0;background:#f8f9fc;">

{{-- ══════════════════════════════════════════════════════════ --}}
{{--  NAVBAR                                                    --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<nav class="wlc-navbar navbar navbar-expand-lg">
    <div class="container-xl">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img src="{{ asset('assets/img/logos/logoonly.jpg') }}" alt="mySalam" height="38"
                 style="border-radius:6px;">
            <span class="navbar-brand-text">mySalam Online</span>
        </a>

        <button class="navbar-toggler border-0 p-1" type="button"
                data-bs-toggle="collapse" data-bs-target="#wlcNav"
                aria-controls="wlcNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bx bx-menu" style="color:#B18752;font-size:1.6rem;"></i>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="wlcNav">
            <ul class="navbar-nav align-items-center gap-2 mt-2 mt-lg-0">
                @auth
                    <li class="nav-item">
                        <a href="{{ url('/dashboard') }}" class="btn btn-sm wlc-btn-gold px-3 py-2">
                            <i class="bx bx-tachometer me-1"></i>Dashboard
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('claim.notify.lookup') }}" class="btn btn-sm wlc-btn-gold px-3 py-2">
                            <i class="bx bx-notepad me-1"></i>Report a Claim
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-sm wlc-btn-outline px-3 py-2">
                            Log in
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn btn-sm btn-outline-light px-3 py-2"
                               style="font-weight:600;">
                                Register
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- ══════════════════════════════════════════════════════════ --}}
{{--  HERO                                                      --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="wlc-hero">
    <div class="container-xl">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <p class="wlc-hero-eyebrow">Salam Takaful Insurance</p>
                <h1>
                    Smarter takaful,<br>
                    <span>at your fingertips.</span>
                </h1>
                <p class="wlc-hero-sub">
                    Buy and manage your policies, report claims, and access your documents —
                    all in one place, 24/7.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="btn btn-lg wlc-btn-gold px-4 py-3">
                            <i class="bx bx-tachometer me-2"></i>Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('claim.notify.lookup') }}"
                           class="btn btn-lg wlc-btn-gold px-4 py-3">
                            <i class="bx bx-notepad me-2"></i>Report a Claim
                        </a>
                        <a href="{{ route('login') }}"
                           class="btn btn-lg btn-outline-light px-4 py-3" style="font-weight:600;">
                            Log in <i class="bx bx-chevron-right ms-1"></i>
                        </a>
                    @endauth
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-flex justify-content-center">
                <img src="{{ asset('assets/img/illustrations/mySalm-welcome.webp') }}"
                     alt="mySalam platform illustration"
                     class="img-fluid wlc-hero-img">
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{--  FEATURES                                                  --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="wlc-features">
    <div class="container-xl">
        <div class="text-center mb-5">
            <h2 class="wlc-section-title">Everything you need, online</h2>
            <p class="wlc-section-sub">Self-service tools designed for customers and agents alike.</p>
        </div>

        <div class="row g-4">

            {{-- Manage Policies --}}
            <div class="col-md-6 col-lg-3">
                <div class="card wlc-feature-card">
                    <div class="card-body p-4">
                        <div class="wlc-icon-wrap" style="background:#161616;">
                            <i class="bx bx-file" style="color:#B18752;"></i>
                        </div>
                        <h5 style="font-weight:700;color:#161616;margin-bottom:.5rem;">Manage Policies</h5>
                        <p style="color:#6b7280;font-size:.9rem;line-height:1.65;margin-bottom:1.5rem;">
                            View, renew, and manage your active takaful policies.
                            Download certificates and track your cover in real time.
                        </p>
                        @auth
                            <a href="{{ route('list_policy') }}"
                               class="btn btn-sm wlc-btn-gold px-3">
                                View Policies <i class="bx bx-chevron-right ms-1"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
                                Log in to view <i class="bx bx-chevron-right ms-1"></i>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Report a Claim — highlighted card --}}
            <div class="col-md-6 col-lg-3">
                <div class="card wlc-feature-card dark">
                    <div class="card-body p-4">
                        <div class="wlc-icon-wrap" style="background:#B18752;">
                            <i class="bx bx-notepad" style="color:#161616;"></i>
                        </div>
                        <h5 style="font-weight:700;color:#fff;margin-bottom:.5rem;">Report a Claim</h5>
                        <p style="color:rgba(255,255,255,.65);font-size:.9rem;line-height:1.65;margin-bottom:1.5rem;">
                            Submit a claim notification directly to our claims team.
                            No account required — just your policy number and email.
                        </p>
                        <a href="{{ route('claim.notify.lookup') }}"
                           class="btn btn-sm wlc-btn-gold px-3">
                            Start Claim <i class="bx bx-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Policy Certificate --}}
            <div class="col-md-6 col-lg-3">
                <div class="card wlc-feature-card">
                    <div class="card-body p-4">
                        <div class="wlc-icon-wrap" style="background:#161616;">
                            <i class="bx bx-award" style="color:#B18752;"></i>
                        </div>
                        <h5 style="font-weight:700;color:#161616;margin-bottom:.5rem;">Policy Certificate</h5>
                        <p style="color:#6b7280;font-size:.9rem;line-height:1.65;margin-bottom:1.5rem;">
                            Reprint your motor policy certificate instantly using your policy number.
                            No login required.
                        </p>
                        <button onclick="document.getElementById('policyvalidation').scrollIntoView({behavior:'smooth'})"
                                class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
                            Get Certificate <i class="bx bx-chevron-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Brown Card --}}
            <div class="col-md-6 col-lg-3">
                <div class="card wlc-feature-card">
                    <div class="card-body p-4">
                        <div class="wlc-icon-wrap" style="background:#161616;">
                            <i class="bx bx-id-card" style="color:#B18752;"></i>
                        </div>
                        <h5 style="font-weight:700;color:#161616;margin-bottom:.5rem;">Brown Card</h5>
                        <p style="color:#6b7280;font-size:.9rem;line-height:1.65;margin-bottom:1.5rem;">
                            Retrieve your ECOWAS Brown Card certificate using your policy or
                            registration number. No login required.
                        </p>
                        <a href="{{ route('browncard.lookup') }}"
                           class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
                            Get Brown Card <i class="bx bx-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{--  SELF-SERVICE TOOLS                                        --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="wlc-tools" id="tools">
    <div class="container-xl">
        <div class="text-center mb-5">
            <h2 class="wlc-section-title">Self-service tools</h2>
            <p class="wlc-section-sub">Get what you need without logging in.</p>
        </div>

        <div class="row g-4 justify-content-center">

            {{-- Policy Certificate Reprint --}}
            <div class="col-md-6 col-lg-4" id="policyvalidation">
                <div class="card wlc-tool-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="wlc-icon-wrap mb-0" style="background:#161616;">
                                <i class="bx bx-award" style="color:#B18752;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0" style="font-weight:700;color:#161616;">Reprint Motor Certificate</h5>
                                <p class="mb-0 small" style="color:#6b7280;">Enter your policy number to download</p>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control"
                                   placeholder="e.g. SLM/MTR/2025/00123"
                                   name="policynumber" id="policynumber">
                            <button class="btn wlc-tool-btn wlc-btn-gold" type="button"
                                    onclick="getCertificate()">
                                <i class="bx bx-download me-1"></i>Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Claim Status Check --}}
            <div class="col-md-6 col-lg-4" id="claimcheck">
                <div class="card wlc-tool-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="wlc-icon-wrap mb-0" style="background:#161616;">
                                <i class="bx bx-search-alt" style="color:#B18752;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0" style="font-weight:700;color:#161616;">Check Claim Status</h5>
                                <p class="mb-0 small" style="color:#6b7280;">Track an existing claim notification</p>
                            </div>
                        </div>
                        <form action="{{ route('claim_check') }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control"
                                       placeholder="Policy or Claim Number"
                                       name="claimnumber" id="claimnumber" required>
                                <button type="submit"
                                        class="btn wlc-tool-btn"
                                        style="background:#161616;color:#B18752;">
                                    <i class="bx bx-search me-1"></i>Check
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Brown Card Lookup --}}
            <div class="col-md-6 col-lg-4" id="browncard">
                <div class="card wlc-tool-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="wlc-icon-wrap mb-0" style="background:#161616;">
                                <i class="bx bx-id-card" style="color:#B18752;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0" style="font-weight:700;color:#161616;">Retrieve Brown Card</h5>
                                <p class="mb-0 small" style="color:#6b7280;">By policy or registration number</p>
                            </div>
                        </div>
                        <a href="{{ route('browncard.lookup') }}" class="btn wlc-tool-btn wlc-btn-gold w-100">
                            <i class="bx bx-download me-1"></i>Get Brown Card
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{--  FOOTER                                                    --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<footer class="wlc-footer">
    <div class="container-xl">
        <div class="row g-4 mb-2">

            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ asset('assets/img/logos/logoonly.jpg') }}"
                         alt="mySalam" height="34" style="border-radius:5px;">
                    <span style="color:#B18752;font-weight:700;font-size:1rem;">mySalam Online</span>
                </div>
                <p style="color:rgba(255,255,255,.5);font-size:.85rem;line-height:1.75;max-width:300px;">
                    Salam Takaful Insurance — a licensed takaful operator
                    regulated by the National Insurance Commission (NAICOM).
                </p>
            </div>

            <div class="col-6 col-md-3 offset-md-1">
                <p style="color:#B18752;font-weight:700;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.85rem;">
                    Quick Links
                </p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <a href="{{ route('claim.notify.lookup') }}"
                           style="color:rgba(255,255,255,.55);text-decoration:none;font-size:.88rem;">
                            <i class="bx bx-notepad me-1"></i>Report a Claim
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('login') }}"
                           style="color:rgba(255,255,255,.55);text-decoration:none;font-size:.88rem;">
                            <i class="bx bx-log-in me-1"></i>Agent Login
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="mb-2">
                            <a href="{{ route('register') }}"
                               style="color:rgba(255,255,255,.55);text-decoration:none;font-size:.88rem;">
                                <i class="bx bx-user-plus me-1"></i>Register
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="col-6 col-md-3">
                <p style="color:#B18752;font-weight:700;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.85rem;">
                    Contact
                </p>
                <p style="color:rgba(255,255,255,.55);font-size:.88rem;line-height:1.75;margin:0;">
                    <i class="bx bx-envelope me-1"></i>
                    <a href="mailto:claims@salamtakafulinsurance.com"
                       style="color:rgba(255,255,255,.55);text-decoration:none;">
                        claims@salamtakafulinsurance.com
                    </a>
                </p>
            </div>

        </div>

        <div class="wlc-footer-divider"></div>

        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <p style="color:rgba(255,255,255,.3);font-size:.78rem;margin:0;">
                &copy; {{ date('Y') }} Salam Takaful Insurance. All rights reserved.
            </p>
            <p style="color:rgba(255,255,255,.3);font-size:.78rem;margin:0;">
                Licensed by NAICOM &nbsp;·&nbsp; Regulated takaful operator
            </p>
        </div>
    </div>
</footer>

@include('partials.scripts')

<script>
    function getCertificate() {
        const no = document.getElementById('policynumber').value.trim();
        if (!no) {
            document.getElementById('policynumber').focus();
            return;
        }
        @php
            $eliteRaw   = config('variables.API_ELITE_URL', '');
            $parsedElite = parse_url($eliteRaw);
            $certOrigin  = ($parsedElite['scheme'] ?? 'http') . '://' . ($parsedElite['host'] ?? '');
        @endphp
        window.open('{{ $certOrigin }}' + '/api/v1/policy/view-certificate?policy_no=' + encodeURIComponent(no), '_blank');
    }

    // Allow Enter key in the certificate input
    document.getElementById('policynumber').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); getCertificate(); }
    });
</script>

</body>
</html>
