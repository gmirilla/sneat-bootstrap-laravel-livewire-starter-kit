<x-layouts.app>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Pending Account Verification</strong>
            <span class="badge bg-warning text-dark">{{ $users->total() }} pending</span>
        </div>
        <div class="card-body pb-1">
            <p class="text-muted small mb-0">
                These accounts were created automatically when a claim notification was submitted.
                Verify the claimant's identity against the linked policy before approving.
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Claim Ref</th>
                        <th>Policy</th>
                        <th>Registered</th>
                        <th style="width:170px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php $claim = $user->claimNotifications->first(); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->telno ?? '—' }}</td>
                            <td>
                                @if ($claim)
                                    <span class="badge bg-light text-dark border" style="font-size:.75rem">{{ $claim->reference_no }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $claim?->policy_no ?? '—' }}</td>
                            <td class="text-muted small">{{ $user->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form action="{{ route('admin.accounts.approve', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Approve account for {{ addslashes($user->name) }}?')">
                                            <i class="bx bx-check me-1"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.accounts.reject', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Reject account for {{ addslashes($user->name) }}?')">
                                            <i class="bx bx-x me-1"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No pending accounts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>

</x-layouts.app>
