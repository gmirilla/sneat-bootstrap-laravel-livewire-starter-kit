<x-layouts.app>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>List of Sub-Agents for {{ $agent->name }}</strong>

            <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#createSubagentModal">
                <i class="fa fa-plus me-2"></i> Register Sub Agent
            </button>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-striped table-sm align-middle" id="agentTable">
                <thead>
                <tr>
                    <th width="5%">S/N</th>
                    <th width="30%">Name</th>
                    <th width="25%">Email</th>
                    <th width="10%">Status</th>
                    <th width="5%">Allocated</th>
                    <th width="5%">Used</th>
                    <th width="20%">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse ($subagents as $sub)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sub->name }}</td>
                        <td>{{ $sub->email }}</td>
                        <td>{{ $sub->getagentdetails()->status ?? 'N/a'}}</td>
                        <td>{{ $sub->getagentdetails()->subcreditassigned ?? 'N/a'}}</td>
                        <td>{{ $sub->getagentdetails()->subcreditused ?? 'N/a'}}</td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">

                                <a href="{{ route('agentprofile', ['uid' => $sub->id]) }}"
                                   class="btn btn-sm btn-primary">
                                    View
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addCreditModal"
                                        data-id="{{ $sub->id }}">
                                    + Credits
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#removeCreditModal"
                                        data-id="{{ $sub->id }}">
                                    - Credits
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No sub agents assigned yet.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

        </div>
    </div>

    {{-- ============================= --}}
    {{-- CREATE SUBAGENT MODAL --}}
    {{-- ============================= --}}
    <div class="modal fade" id="createSubagentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route('register_sub_agent', $agent) }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">
                            New Sub Agent
                            <small class="text-muted d-block">
                                Credits Available: {{ $availableCredits }}
                            </small>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address (Optional)</label>
                            <input type="text" name="address" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Initial Credit</label>
                            <input type="number"
                                   name="subcredit"
                                   class="form-control"
                                   min="0"
                                   max="{{ $availableCredits }}"
                                   required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Register</button>
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    {{-- ============================= --}}
    {{-- ADD CREDIT MODAL --}}
    {{-- ============================= --}}
    <div class="modal fade" id="addCreditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route('subagent.credit.add') }}">
                    @csrf

                    <input type="hidden" name="subagent_id" id="add_subagent_id">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Add Credits
                            <small class="text-muted d-block">
                                Credits Available: {{ $availableCredits }}
                            </small>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="alert alert-warning small">
                            Adding credits will reduce the main agent’s available credits.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Credits to Add</label>
                            <input type="number"
                                   name="credits"
                                   class="form-control"
                                   min="1"
                                   max="{{ $availableCredits }}"
                                   required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">Add Credits</button>
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    {{-- ============================= --}}
    {{-- REMOVE CREDIT MODAL --}}
    {{-- ============================= --}}
    <div class="modal fade" id="removeCreditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="{{ route('subagent.credit.remove') }}">
                    @csrf

                    <input type="hidden" name="subagent_id" id="remove_subagent_id">

                    <div class="modal-header">
                        <h5 class="modal-title">Remove Credits</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="alert alert-warning small">
                            Removing credits will increase the main agent’s available credits.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Credits to Remove</label>
                            <input type="number"
                                   name="credits"
                                   class="form-control"
                                   min="1"
                                   required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-danger">Remove Credits</button>
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    {{-- DataTables --}}
    <link rel="stylesheet"
          href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css">

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>

    <script>
        new DataTable('#agentTable');

        const addModal = document.getElementById('addCreditModal');
        const removeModal = document.getElementById('removeCreditModal');

        addModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('add_subagent_id').value =
                button.getAttribute('data-id');
        });

        removeModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('remove_subagent_id').value =
                button.getAttribute('data-id');
        });
    </script>

</x-layouts.app>
