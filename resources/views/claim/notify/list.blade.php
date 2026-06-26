<x-layouts.app>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <strong>{{ $isAdmin ? 'All Claim Notifications' : 'My Claim Notifications' }}</strong>
            <span class="badge bg-secondary">{{ $notifications->total() }} total</span>
        </div>

        {{-- Filters (admin gets search + status; user gets status only) --}}
        <div class="card-body pb-0">
            <form method="GET" action="{{ route('claim.notifications') }}" class="row g-2 align-items-end">
                @if ($isAdmin)
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Ref, policy no, name or email…"
                               value="{{ request('search') }}">
                    </div>
                @endif
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        <option value="submitted"     {{ request('status') === 'submitted'     ? 'selected' : '' }}>Submitted</option>
                        <option value="acknowledged"  {{ request('status') === 'acknowledged'  ? 'selected' : '' }}>Acknowledged</option>
                        <option value="closed"        {{ request('status') === 'closed'        ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bx bx-filter-alt me-1"></i>Filter
                    </button>
                    @if (request('search') || request('status'))
                        <a href="{{ route('claim.notifications') }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Reference</th>
                            @if ($isAdmin)
                                <th>Claimant</th>
                            @endif
                            <th>Policy No</th>
                            <th>Type</th>
                            <th>Incident Date</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            @if ($isAdmin)
                                <th style="width:160px">Update Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $n)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border" style="font-size:.8rem">
                                        {{ $n->reference_no }}
                                    </span>
                                </td>

                                @if ($isAdmin)
                                    <td>
                                        <div class="fw-semibold">{{ $n->claimant_name }}</div>
                                        <div class="text-muted small">{{ $n->claimant_email }}</div>
                                        <div class="text-muted small">{{ $n->claimant_phone }}</div>
                                    </td>
                                @endif

                                <td class="fw-semibold">{{ $n->policy_no }}</td>
                                <td>{{ $n->policy_type }}</td>
                                <td>{{ $n->incident_date->format('d M Y') }}</td>
                                <td class="text-muted small">{{ $n->created_at->format('d M Y H:i') }}</td>

                                <td>
                                    @php
                                        $badge = match($n->status) {
                                            'acknowledged' => 'bg-warning text-dark',
                                            'closed'       => 'bg-success',
                                            default        => 'bg-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ ucfirst($n->status) }}</span>
                                </td>

                                @if ($isAdmin)
                                    <td>
                                        <form action="{{ route('claim.notifications.status', $n) }}" method="POST"
                                              class="d-flex gap-1">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm"
                                                    style="min-width:110px">
                                                <option value="submitted"    {{ $n->status === 'submitted'    ? 'selected' : '' }}>Submitted</option>
                                                <option value="acknowledged" {{ $n->status === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                                                <option value="closed"       {{ $n->status === 'closed'       ? 'selected' : '' }}>Closed</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>

                            {{-- Description row (collapsed, visible on click) --}}
                            <tr class="table-light border-0" id="desc-{{ $n->id }}" style="display:none">
                                <td colspan="{{ $isAdmin ? 8 : 6 }}" class="px-3 py-2">
                                    <p class="mb-1 text-muted small fw-semibold">Description</p>
                                    <p class="mb-1" style="white-space:pre-wrap">{{ $n->description }}</p>
                                    <p class="mb-0 text-muted small">
                                        Policy period: {{ $n->policy_start->format('d M Y') }} – {{ $n->policy_end->format('d M Y') }}
                                        &nbsp;|&nbsp;
                                        Source: {{ ucfirst($n->policy_source) }}
                                        @if ($isAdmin && $n->user)
                                            &nbsp;|&nbsp; Account:
                                            <span class="badge {{ $n->user->account_status === 'active' ? 'bg-success' : ($n->user->account_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                {{ ucfirst($n->user->account_status) }}
                                            </span>
                                        @endif
                                    </p>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 6 }}"
                                    class="text-center text-muted py-5">
                                    @if ($isAdmin)
                                        No claim notifications found.
                                    @else
                                        You have not submitted any claim notifications yet.
                                        <br>
                                        <a href="{{ route('claim.notify.lookup') }}" class="btn btn-sm btn-primary mt-2">
                                            Submit a Claim Notification
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer">{{ $notifications->links() }}</div>
        @endif
    </div>

    {{-- Toggle description rows on reference click --}}
    <script>
        document.querySelectorAll('tbody tr td:first-child .badge').forEach(function (badge) {
            badge.style.cursor = 'pointer';
            badge.title = 'Click to expand description';
            badge.addEventListener('click', function () {
                var row = badge.closest('tr').nextElementSibling;
                if (row && row.id && row.id.startsWith('desc-')) {
                    row.style.display = row.style.display === 'none' ? '' : 'none';
                }
            });
        });
    </script>

</x-layouts.app>
