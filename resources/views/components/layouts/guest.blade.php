<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{ url('/') }}"
      data-framework="laravel">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Salam Takaful Insurance' }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/mysalamcustom.css') }}">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background: #161616">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('assets/img/logos/logoonly.jpg') }}" alt="Logo" class="me-2" style="height:40px;">
            <span class="fw-bold" style="color: #B18752">SALAM TAKAFUL INSURANCE</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
                aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarMenu" >
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="{{ route('login') }}" style="color: #B18752">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="{{ route('register') }}" style="color: #B18752">Register</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- /Navbar -->

<!-- Main Content -->
<main class="flex-grow-1 py-5">
    <div class="container">
        {{ $slot }}
    </div>
</main>

<!-- Footer -->
<footer class="bg-white shadow-sm mt-auto py-4">
    <div class="container text-center">
        <x-layouts.footer.default :title="$title ?? null"></x-layouts.footer.default>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.scripts')
</body>
</html>