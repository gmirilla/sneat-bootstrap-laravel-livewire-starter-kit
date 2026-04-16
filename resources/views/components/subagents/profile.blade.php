
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
                {{-- Pool mode --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Credit Mode:</strong>
                        <span class="badge bg-primary ms-1">Shared Pool</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Personal Cap:</strong>
                        {{ $agent->pool_cap > 0 ? $agent->pool_cap : 'Unlimited' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Cap Used:</strong> {{ $agent->pool_cap_used }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Pool Size:</strong> {{ $parentDetails->pool_size }}
                    </div>
                    <div class="col-md-4">
                        <strong>Pool Used:</strong> {{ $parentDetails->pool_used }}
                    </div>
                    <div class="col-md-4">
                        <strong>Pool Available:</strong>
                        {{ $parentDetails->pool_size - $parentDetails->pool_used }}
                    </div>
                </div>
            @else
                {{-- Individual allocation mode --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Credit Mode:</strong>
                        <span class="badge bg-secondary ms-1">Individual</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Total Credits Assigned:</strong> {{ $agent->subcreditassigned }}
                    </div>
                    <div class="col-md-4">
                        <strong>Total Credits Used:</strong> {{ $agent->subcreditused }}
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Parent Agent:</strong> {{ $agent->getparentdetails()->name  }}
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
                    <p class="text-danger"> Minimum Password Length is 6 Characters</p>

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
