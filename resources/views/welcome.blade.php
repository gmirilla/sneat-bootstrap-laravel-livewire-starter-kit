<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{ url('/') }}" data-framework="laravel">

@section('title', __('Welcome'))
<head>
    @include('partials.head')

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/mysalamcustom.css') }}">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

<div class="container-xxl flex-grow-1 container-py py-5">

    <!-- Auth Buttons -->
    <div class="d-flex justify-content-end mb-4">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-secondary me-2">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @endif
            @endauth
        @endif
    </div>

    <!-- Welcome Card -->
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-10 col-md-11 col-sm-12">
            <div class="card shadow-lg rounded-4 overflow-hidden">
                <div class="row g-0">

                    <!-- Left Text / Forms -->
                    <div class="col-md-6 d-flex align-items-center" style="background-color: #161616;">
                        <div class="card-body p-5" style="color: #B18752">
                            <h1 class="h4 fw-bold mb-3">Welcome to <i>mySalam Online</i></h1>
                            <p class="mb-4">
                                Streamlined, efficient, and customer-focused, we empower customers and agents.
                                Accelerate your journey to smarter takaful solutions today!
                            </p>

                            <!-- Policy Certificate Form -->
                            <div id="policyvalidation" class="mb-4">
                                <p class="fw-semibold">Reprint your policy certificate:</p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Enter Policy Number" name="policynumber" id="policynumber" required>
                                    <button class="btn btn-primary" type="button" onclick="getCertificate()">Get Certificate</button>
                                </div>
                            </div>

                            <!-- Claim Status Form -->
                            <div id="claimcheck">
                                <p class="fw-semibold">Check your claim status:</p>
                                <form action="{{ route('claim_check') }}" method="post" class="d-flex">
                                    @csrf
                                    <input type="text" class="form-control me-2" placeholder="Policy or Claim Number" name="claimnumber" id="claimnumber" required>
                                    <button type="submit" class="btn btn-primary">Get Status</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Image -->
                    <div class="col-md-6 d-none d-md-block">
                        <img src="{{ asset('assets/img/illustrations/mySalm-welcome.webp') }}" class="img-fluid h-100 w-100 object-fit-cover" alt="Welcome Illustration">
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- Footer -->
<footer class="bg-white text-center py-4 mt-auto shadow-sm">
    <p class="mb-0">&copy; {{ date('Y') }} Salam Takaful Insurance. All rights reserved.</p>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.scripts')

<script>
    function getCertificate() {
        const policynumber = document.getElementById('policynumber').value.trim();
        if(policynumber) {
            window.open(
                `http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no=${policynumber}`,
                '_blank'
            );
        }
    }
</script>

</body>
</html>