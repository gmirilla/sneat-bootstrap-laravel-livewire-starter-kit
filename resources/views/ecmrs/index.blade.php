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
                <h5 class="card-title">Search Vehicle Record</h5>
                <form action="" method="post">
                    <label for="ecmr_regno">Registration Number</label>
                    <input type="text" id="ecmr_regno" name="ecmr_regno" class="form-control mb-3" required>
                    <button type="submit" class="btn btn-primary update-btn">Search</button>
                </form>
            </div>
        </div>
        </div>

    <div class="row g-4">
        <div class="card">
            <div class="card-body table-responsive">
            <div>
                <table id="ecmrTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Policy ID</th>
                            <th>CUID</th>
                            <th>Licence Plate</th>
                            <th>Response</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>CMR Number</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ecmrs as $ecmr)
                            <tr>
                                <td>{{ $ecmr->id }}</td>
                                <td>{{ $ecmr->policy_id }}</td>
                                <td>{{ $ecmr->cuid }}</td>
                                <td>{{ $ecmr->licence_plate }}</td>
                                <td>{{ $ecmr->response }}</td>
                                <td>{{ $ecmr->status }}</td>
                                <td>{{ $ecmr->message }}</td>
                                <td>{{ $ecmr->cmr_number }}</td>
                                <td>{{ $ecmr->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
