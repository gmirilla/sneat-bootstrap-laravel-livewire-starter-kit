<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">
  @section('title', __('Welcome'))
  <head>
    @include('partials.head')
  </head>
  <body>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-end mb-3">
          @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary me-2">Log in</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @endif
            @endauth
          @endif
      </div>
      <div class="col col-xl col-sm col-md col-8 mx-auto py-auto" style="height: 100vh ; padding-top:5%">
        <div class="card col-auto">
          <div class="row g-0" style="background-color: rgb(19, 19, 60)">
            <div class="col-sm d-flex align-items-center">
              <div class="card-body" style="color: white">
                <h1 class="h4 card-title" style="color: white">Welcome to <i>mySalam Online</i></h1>
                <p class="card-text mb-5">
                  Streamlined, efficient, and customer-focused, we’re here to empower customers and agents.
                   Let’s accelerate your journey to smarter takaful solutions today!</p>
                   <div id="policyvalidation">
                    <p>You can easily reprint your policy Certificate here:</p>
                    <form action="" method="get">
                      <input type="text" class="form-control mb-3" placeholder="Enter Policy Number for Certificate" name="policynumber" id="policynumber" style="color:white; background-color: grey;" required>
                      <button type="button" class="btn btn-primary" onclick="getCertificate()">Get Policy Certificate</button>
          
                    </form>
                   </div>
                                      <div id="claimcheck">
                    <p>You can easily check the status of your claim:</p>
                    <form action="{{ route('claim_check') }}" method="post">
                      @csrf
                      <input type="text" class="form-control mb-3" placeholder="Enter Policy Number  or Claim Number" name="claimnumber" id="claimnumber" style="color:white; background-color: grey;" required>
                      <button type="submit" class="btn btn-primary">Get Claim Status</button>
          
                    </form>
                   </div>
              
              </div>
            </div>
            <div class="col-sm">
              <img class="card-img card-img-right" src="{{asset('assets/img/illustrations/mySalm-welcome.png')}}" alt="Card image">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Include Scripts -->
    @include('partials.scripts')
    <!-- / Include Scripts -->
    <script>
      function getCertificate(){
        const policynumber = document.getElementById('policynumber').value;
        console.log(policynumber);
        window.location.href= "http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no="+policynumber;
      } 
      </script>
  </body>
</html>
