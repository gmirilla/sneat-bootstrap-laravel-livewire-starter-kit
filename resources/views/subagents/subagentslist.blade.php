<x-layouts.app>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Pool Management Card ──────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="fa fa-cubes me-2"></i>Shared Credit Pool</strong>
            @if ($agentDetails->pool_enabled)
                <span class="badge bg-success">Pool Active</span>
            @else
                <span class="badge bg-secondary">Pool Inactive</span>
            @endif
        </div>

        <div class="card-body">
            @if ($agentDetails->pool_enabled)
                {{-- Pool stats split by type --}}
                <div class="row text-center mb-3">
                    <div class="col-12 mb-2"><strong class="text-muted small text-uppercase">Private Pool</strong></div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fs-5 fw-bold text-primary">{{ $agentDetails->pool_private_size }}</div>
                            <small class="text-muted">Size</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fs-5 fw-bold text-danger">{{ $agentDetails->pool_private_used }}</div>
                            <small class="text-muted">Used</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fs-5 fw-bold text-success">{{ $agentDetails->pool_private_size - $agentDetails->pool_private_used }}</div>
                            <small class="text-muted">Available</small>
                        </div>
                    </div>
                </div>
                <div class="row text-center mb-3">
                    <div class="col-12 mb-2"><strong class="text-muted small text-uppercase">Commercial Pool</strong></div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fs-5 fw-bold text-primary">{{ $agentDetails->pool_commercial_size }}</div>
                            <small class="text-muted">Size</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fs-5 fw-bold text-danger">{{ $agentDetails->pool_commercial_used }}</div>
                            <small class="text-muted">Used</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fs-5 fw-bold text-success">{{ $agentDetails->pool_commercial_size - $agentDetails->pool_commercial_used }}</div>
                            <small class="text-muted">Available</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal" data-bs-target="#resizePoolModal">
                        <i class="fa fa-edit me-1"></i> Resize Pool
                    </button>
                    <form method="POST" action="{{ route('subagent.pool.update') }}"
                          onsubmit="return confirm('Disable the pool? Unused credits will be returned to your balance.')">
                        @csrf
                        <input type="hidden" name="action" value="disable">
                        <input type="hidden" name="pool_private_size" value="0">
                        <input type="hidden" name="pool_commercial_size" value="0">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-times me-1"></i> Disable Pool
                        </button>
                    </form>
                </div>

            @else
                <p class="text-muted small mb-2">
                    Enable a shared pool to let all your sub-agents draw from a common credit balance.<br>
                    Available — Private: <strong>{{ $availablePrivate }}</strong> &nbsp;|&nbsp;
                    Commercial: <strong>{{ $availableCommercial }}</strong>
                </p>
                <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal" data-bs-target="#enablePoolModal">
                    <i class="fa fa-power-off me-1"></i> Enable Credit Pool
                </button>
            @endif
        </div>
    </div>

    {{-- ── Sub-agents Table ─────────────────────────────────────────────── --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Sub-Agents for {{ $agent->name }}</strong>
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
                    <th width="4%">S/N</th>
                    <th width="24%">Name</th>
                    <th width="20%">Email</th>
                    <th width="8%">Status</th>
                    @if ($agentDetails->pool_enabled)
                        <th width="9%">Priv Cap</th>
                        <th width="9%">Priv Used</th>
                        <th width="9%">Comm Cap</th>
                        <th width="9%">Comm Used</th>
                    @else
                        <th width="9%">Priv Alloc</th>
                        <th width="9%">Priv Used</th>
                        <th width="9%">Comm Alloc</th>
                        <th width="9%">Comm Used</th>
                    @endif
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse ($subagents as $sub)
                    @php $subDetails = $sub->getagentdetails(); @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sub->name }}</td>
                        <td>{{ $sub->email }}</td>
                        <td>{{ $subDetails->status ?? 'N/A' }}</td>
                        @if ($agentDetails->pool_enabled)
                            <td>{{ ($subDetails->pool_cap_private ?? 0) > 0 ? $subDetails->pool_cap_private : '∞' }}</td>
                            <td>{{ $subDetails->pool_cap_used_private ?? 0 }}</td>
                            <td>{{ ($subDetails->pool_cap_commercial ?? 0) > 0 ? $subDetails->pool_cap_commercial : '∞' }}</td>
                            <td>{{ $subDetails->pool_cap_used_commercial ?? 0 }}</td>
                        @else
                            <td>{{ $subDetails->subcreditassigned_private ?? 0 }}</td>
                            <td>{{ $subDetails->subcreditused_private ?? 0 }}</td>
                            <td>{{ $subDetails->subcreditassigned_commercial ?? 0 }}</td>
                            <td>{{ $subDetails->subcreditused_commercial ?? 0 }}</td>
                        @endif
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('subagent.profile', ['sid' => $sub->id]) }}"
                                   class="btn btn-sm btn-primary">View</a>
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
                        <td colspan="9" class="text-center text-muted">No sub agents assigned yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- ── ENABLE POOL MODAL ────────────────────────────────────────────── --}}
    <div class="modal fade" id="enablePoolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('subagent.pool.update') }}">
                    @csrf
                    <input type="hidden" name="action" value="enable">
                    <div class="modal-header">
                        <h5 class="modal-title">Enable Credit Pool</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small">
                            Credits committed to the pool are reserved from your balance.
                            Sub-agents draw automatically from the pool.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Private Pool Size</label>
                            <input type="number" name="pool_private_size" class="form-control"
                                   min="0" max="{{ $availablePrivate }}" value="0" required>
                            <div class="form-text">Available: {{ $availablePrivate }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Commercial Pool Size</label>
                            <input type="number" name="pool_commercial_size" class="form-control"
                                   min="0" max="{{ $availableCommercial }}" value="0" required>
                            <div class="form-text">Available: {{ $availableCommercial }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Enable Pool</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ── RESIZE POOL MODAL ────────────────────────────────────────────── --}}
    <div class="modal fade" id="resizePoolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('subagent.pool.update') }}">
                    @csrf
                    <input type="hidden" name="action" value="resize">
                    <div class="modal-header">
                        <h5 class="modal-title">Resize Credit Pool</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Private Pool Size</label>
                            <input type="number" name="pool_private_size" class="form-control"
                                   min="{{ $agentDetails->pool_private_used }}"
                                   value="{{ $agentDetails->pool_private_size }}" required>
                            <div class="form-text">
                                Min: {{ $agentDetails->pool_private_used }} consumed.
                                Free private credits: {{ $availablePrivate }}.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Commercial Pool Size</label>
                            <input type="number" name="pool_commercial_size" class="form-control"
                                   min="{{ $agentDetails->pool_commercial_used }}"
                                   value="{{ $agentDetails->pool_commercial_size }}" required>
                            <div class="form-text">
                                Min: {{ $agentDetails->pool_commercial_used }} consumed.
                                Free commercial credits: {{ $availableCommercial }}.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ── REGISTER SUBAGENT MODAL ──────────────────────────────────────── --}}
    <div class="modal fade" id="createSubagentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('register_sub_agent', $agent) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">New Sub Agent</h5>
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
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Private Credits
                                @if ($agentDetails->pool_enabled)
                                    <span class="text-muted fw-normal">(personal cap, 0 = unlimited)</span>
                                @else
                                    <span class="text-muted fw-normal">(available: {{ $availablePrivate }})</span>
                                @endif
                            </label>
                            <input type="number" name="subcredit_private" class="form-control"
                                   min="0" @if(!$agentDetails->pool_enabled) max="{{ $availablePrivate }}" @endif
                                   value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Commercial Credits
                                @if ($agentDetails->pool_enabled)
                                    <span class="text-muted fw-normal">(personal cap, 0 = unlimited)</span>
                                @else
                                    <span class="text-muted fw-normal">(available: {{ $availableCommercial }})</span>
                                @endif
                            </label>
                            <input type="number" name="subcredit_commercial" class="form-control"
                                   min="0" @if(!$agentDetails->pool_enabled) max="{{ $availableCommercial }}" @endif
                                   value="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Register</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ── ADD CREDITS MODAL ────────────────────────────────────────────── --}}
    <div class="modal fade" id="addCreditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('subagent.credit.add') }}">
                    @csrf
                    <input type="hidden" name="subagent_id" id="add_subagent_id">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $agentDetails->pool_enabled ? 'Increase Sub-Agent Cap' : 'Add Credits' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($agentDetails->pool_enabled)
                            <div class="alert alert-info small">
                                Raising a cap does not consume pool credits — it only raises how much this
                                sub-agent may draw from the shared pool.
                            </div>
                        @else
                            <div class="alert alert-warning small">
                                Adding credits deducts from your available balance.<br>
                                Private available: <strong>{{ $availablePrivate }}</strong> &nbsp;|&nbsp;
                                Commercial available: <strong>{{ $availableCommercial }}</strong>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Credit Type</label>
                            <select name="credit_type" class="form-select" required>
                                <option value="private">Private</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount</label>
                            <input type="number" name="credits" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">
                            {{ $agentDetails->pool_enabled ? 'Increase Cap' : 'Add Credits' }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ── REMOVE CREDITS MODAL ─────────────────────────────────────────── --}}
    <div class="modal fade" id="removeCreditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('subagent.credit.remove') }}">
                    @csrf
                    <input type="hidden" name="subagent_id" id="remove_subagent_id">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $agentDetails->pool_enabled ? 'Reduce Sub-Agent Cap' : 'Remove Credits' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small">
                            @if ($agentDetails->pool_enabled)
                                You can only reduce the cap by the amount the sub-agent has not yet consumed.
                            @else
                                Only unused credits can be removed (assigned minus used).
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Credit Type</label>
                            <select name="credit_type" class="form-select" required>
                                <option value="private">Private</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount</label>
                            <input type="number" name="credits" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger">
                            {{ $agentDetails->pool_enabled ? 'Reduce Cap' : 'Remove Credits' }}
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

        document.getElementById('addCreditModal').addEventListener('show.bs.modal', function (event) {
            document.getElementById('add_subagent_id').value =
                event.relatedTarget.getAttribute('data-id');
        });

        document.getElementById('removeCreditModal').addEventListener('show.bs.modal', function (event) {
            document.getElementById('remove_subagent_id').value =
                event.relatedTarget.getAttribute('data-id');
        });
    </script>

</x-layouts.app>
