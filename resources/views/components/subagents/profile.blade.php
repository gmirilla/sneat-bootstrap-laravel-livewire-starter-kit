
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Sub Agent Profile</h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Name:</strong> {{ $user->firstname }} {{ $user->lastname }}
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong> {{ $user->email }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Phone:</strong> {{ $user->telno }}
                </div>
                <div class="col-md-6">
                    <strong>Role:</strong> {{ ucfirst($user->role) }}
                </div>
            </div>

            @php $parentDetails = $agent->parentAgentDetails(); @endphp

            @if ($parentDetails && $parentDetails->pool_enabled)
                {{-- Pool mode: show per-type pool stats and this sub-agent's caps --}}
                <div class="mb-2">
                    <span class="badge bg-primary">Shared Pool Mode</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center mb-3">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>Private</th>
                                <th>Commercial</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start fw-semibold">Pool Size</td>
                                <td>{{ $parentDetails->pool_private_size }}</td>
                                <td>{{ $parentDetails->pool_commercial_size }}</td>
                                <td>{{ $parentDetails->pool_private_size + $parentDetails->pool_commercial_size }}</td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">Pool Used</td>
                                <td>{{ $parentDetails->pool_private_used }}</td>
                                <td>{{ $parentDetails->pool_commercial_used }}</td>
                                <td>{{ $parentDetails->pool_private_used + $parentDetails->pool_commercial_used }}</td>
                            </tr>
                            <tr class="table-success">
                                <td class="text-start fw-semibold">Pool Available</td>
                                <td>{{ $parentDetails->pool_private_size - $parentDetails->pool_private_used }}</td>
                                <td>{{ $parentDetails->pool_commercial_size - $parentDetails->pool_commercial_used }}</td>
                                <td>{{ ($parentDetails->pool_private_size - $parentDetails->pool_private_used) + ($parentDetails->pool_commercial_size - $parentDetails->pool_commercial_used) }}</td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">My Cap</td>
                                <td>{{ ($agent->pool_cap_private ?? 0) > 0 ? $agent->pool_cap_private : '∞' }}</td>
                                <td>{{ ($agent->pool_cap_commercial ?? 0) > 0 ? $agent->pool_cap_commercial : '∞' }}</td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">My Cap Used</td>
                                <td>{{ $agent->pool_cap_used_private ?? 0 }}</td>
                                <td>{{ $agent->pool_cap_used_commercial ?? 0 }}</td>
                                <td>{{ ($agent->pool_cap_used_private ?? 0) + ($agent->pool_cap_used_commercial ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            @else
                {{-- Individual allocation mode --}}
                <div class="mb-2">
                    <span class="badge bg-secondary">Individual Allocation Mode</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center mb-3">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>Private</th>
                                <th>Commercial</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start fw-semibold">Assigned</td>
                                <td>{{ $agent->subcreditassigned_private ?? 0 }}</td>
                                <td>{{ $agent->subcreditassigned_commercial ?? 0 }}</td>
                                <td>{{ ($agent->subcreditassigned_private ?? 0) + ($agent->subcreditassigned_commercial ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="text-start fw-semibold">Used</td>
                                <td>{{ $agent->subcreditused_private ?? 0 }}</td>
                                <td>{{ $agent->subcreditused_commercial ?? 0 }}</td>
                                <td>{{ ($agent->subcreditused_private ?? 0) + ($agent->subcreditused_commercial ?? 0) }}</td>
                            </tr>
                            <tr class="table-success">
                                <td class="text-start fw-semibold">Remaining</td>
                                <td>{{ ($agent->subcreditassigned_private ?? 0) - ($agent->subcreditused_private ?? 0) }}</td>
                                <td>{{ ($agent->subcreditassigned_commercial ?? 0) - ($agent->subcreditused_commercial ?? 0) }}</td>
                                <td>{{ (($agent->subcreditassigned_private ?? 0) - ($agent->subcreditused_private ?? 0)) + (($agent->subcreditassigned_commercial ?? 0) - ($agent->subcreditused_commercial ?? 0)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Parent Agent:</strong> {{ $agent->getparentdetails()->name }}
                </div>
                <div class="col-md-6">
                    <strong>Created At:</strong> {{ $user->created_at }}
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                    Reset Password
                </button>
            </div>

        </div>
    </div>
</div>

<!-- RESET PASSWORD MODAL -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('subagent.resetpassword', $user->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for:</p>
                    <h6 class="fw-bold">{{ $user->firstname }} {{ $user->lastname }}</h6>
                    <p class="text-danger">Minimum Password Length is 6 Characters</p>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
