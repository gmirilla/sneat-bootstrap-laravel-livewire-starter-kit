<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">
  <head>
    @include('partials.head')
    


  </head>
  <link rel="stylesheet" href="{{ asset('css/mysalamcustom.css') }}">
    <body>

    <div class="layout-wrapper layout-content-navbar">
        <x-layouts.navbar.unauth :title="$title ?? null"></x-layouts.navbar.unauth>
      <div class="layout-container">

        <!-- Layout Content -->
        <!--/ Layout Content -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          
          <!--/ Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              {{ $slot }}
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <x-layouts.footer.default :title="$title ?? null"></x-layouts.footer.default>
            <!--/ Footer -->
            <div class="content-backdrop fade"></div>
            <!-- / Content wrapper -->
          </div>
        </div>
        <!-- / Layout page -->
      </div>
    </div>

    <!-- Include Scripts -->
    @include('partials.scripts')
    <!-- / Include Scripts -->



  </body>
</html>