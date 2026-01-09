<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css"></script>
<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
</style>

<div id="loadingOverlay">
    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>


<x-layouts.app>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;">
        @if (session('success'))
            <div class="toast align-items-center text-bg-success border-0 show">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast align-items-center text-bg-danger border-0 show">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="card">
            <div class="card-body">
                <div class="flex justify-content-around flex-lg-row flex-md-row">
                    <div style="background-color: rgba(201, 198, 198, 0.224) ; border-radius: 10%;" class="p-3  mb-3">
                        <h5>Total Vehicle Makes : {{ $totalMakes }}</h5>
                        <a href="{{ route('updatevmake') }}" class="btn btn-primary update-btn">UPDATE VEHICLE MAKE</a>

                    </div>
                    <div style="background-color: rgba(201, 198, 198, 0.224) ; border-radius: 10%;" class="p-3  mb-3">
                        <h5>Total Vehicle Colors : {{ $totalColors }}</h5>

                        <a href="{{ route('updatecolors') }}"class="btn btn-primary update-btn">UPDATE VEHICLE
                            COLORS</a>
                    </div>
                    <div style="background-color: rgba(201, 198, 198, 0.224) ; border-radius: 10%;" class="p-3 mb-3">
                        <h5>Total States: {{ $totalStates }}</h5>
                        <a href="{{ route('updatestates') }}" class="btn btn-primary update-btn">UPDATE STATE & LGAS</a>
                    </div>

                </div>

            </div>


        </div>

    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const overlay = document.getElementById("loadingOverlay");

            document.querySelectorAll(".update-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    overlay.style.display = "flex";
                });
            });
        });
    </script>
</x-layouts.app>
