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
                <form action="{{route('ecmr.check')}}" method="get">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ecmrs as $ecmr)
                            <tr>
                                <td>{{ $ecmr->id }}</td>
                                <td>{{ $ecmr->policy_id }}</td>
                                <td>{{ $ecmr->user->name ?? 'N/A' }}</td>
                                <td>{{ $ecmr->licence_plate }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary response-trigger"
                                        data-cmr="{{ $ecmr->cmr_number }}"
                                        data-response="{{ htmlspecialchars(json_encode(json_decode($ecmr->response), JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') }}">
                                        View Response
                                    </button>
                                </td>
                                <td>{{ $ecmr->status }}</td>
                                <td>{{ $ecmr->message }}</td>
                                <td>{{ $ecmr->cmr_number }}</td>
                                <td>{{ $ecmr->created_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning retry-btn"
                                        data-plate="{{ $ecmr->licence_plate }}"
                                        type="button">
                                        <i class="fa-solid fa-rotate-right"></i> Retry
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>

    <!-- Hidden retry form — reuses the same ecmr.check GET route -->
    <form id="retryForm" action="{{ route('ecmr.check') }}" method="get" style="display:none">
        <input type="hidden" id="retryPlate" name="ecmr_regno">
    </form>

    <!-- Single shared Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">API Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre id="responseModalBody"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

            // ── Shared response modal ──────────────────────────────
            const responseModal     = new bootstrap.Modal(document.getElementById('responseModal'));
            const responseModalLabel = document.getElementById('responseModalLabel');
            const responseModalBody  = document.getElementById('responseModalBody');

            // ── Retry ECMR ────────────────────────────────────────
            const retryForm  = document.getElementById('retryForm');
            const retryPlate = document.getElementById('retryPlate');

            document.querySelectorAll('.retry-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    retryPlate.value = btn.getAttribute('data-plate');
                    overlay.style.display = 'flex';
                    retryForm.submit();
                });
            });

            document.querySelectorAll('.response-trigger').forEach(btn => {
                btn.addEventListener('click', () => {
                    responseModalLabel.textContent = 'API Response for CMR #' + btn.getAttribute('data-cmr');
                    responseModalBody.textContent  = btn.getAttribute('data-response');
                    responseModal.show();
                });
            });
        });

        // ── DataTable ─────────────────────────────────────────────
        new DataTable('#ecmrTable', {
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel"></i> Excel', title: 'ECMR List' },
                { extend: 'pdfHtml5',   text: '<i class="fa-solid fa-file-pdf"></i> PDF',     title: 'ECMR List', orientation: 'landscape', pageSize: 'A4' }
            ]
        });
    </script>
</x-layouts.app>
