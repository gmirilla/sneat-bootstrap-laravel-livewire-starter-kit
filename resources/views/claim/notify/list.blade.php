<x-layouts.app>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <strong>{{ $isAdmin ? 'All Claim Notifications' : 'My Claim Notifications' }}</strong>
            <span class="badge bg-secondary">{{ $notifications->total() }} total</span>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('claim.notifications') }}" class="row g-2 align-items-end">
                @if ($isAdmin)
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Ref, Elite no, policy, name or email…"
                               value="{{ request('search') }}">
                    </div>
                @endif
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        <option value="received"   {{ request('status') === 'received'   ? 'selected' : '' }}>Received</option>
                        <option value="registered" {{ request('status') === 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="closed"     {{ request('status') === 'closed'     ? 'selected' : '' }}>Closed</option>
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

        @if ($isAdmin)
            <div class="card-body pt-2 pb-0">
                <span class="badge border me-1" style="background:#fef9c3;color:#78350f;border-color:#fde68a!important">
                    ■ Unregistered &gt;2 days
                </span>
                <span class="badge border" style="background:#fee2e2;color:#7f1d1d;border-color:#fca5a5!important">
                    ■ Unregistered &gt;5 days
                </span>
            </div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>MySalam Ref</th>
                            @if ($isAdmin)<th>Claimant</th>@endif
                            <th>Policy No</th>
                            <th>Type</th>
                            <th>Incident</th>
                            <th>Received</th>
                            <th>Status</th>
                            <th>Elite Claim No</th>
                            @if ($isAdmin)<th style="width:130px">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $n)
                            @php
                                $ageDays     = $n->created_at->diffInDays(now());
                                $unregistered = !$n->elite_claim_no && $n->status === 'received';
                                $rowClass    = '';
                                if ($isAdmin && $unregistered) {
                                    $rowClass = $ageDays >= 5 ? 'table-danger' : ($ageDays >= 2 ? 'table-warning' : '');
                                }
                                $statusBadge = match($n->status) {
                                    'registered' => 'bg-success',
                                    'closed'     => 'bg-secondary',
                                    default      => 'bg-primary',
                                };
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td>
                                    <span class="badge bg-light text-dark border ref-toggle"
                                          data-target="desc-{{ $n->id }}"
                                          style="cursor:pointer;font-size:.8rem"
                                          title="Click to expand">
                                        {{ $n->reference_no }}
                                    </span>
                                    @if ($n->claim_attachments_count > 0)
                                        <span class="badge bg-light text-muted border ms-1"
                                              style="font-size:.7rem"
                                              title="{{ $n->claim_attachments_count }} attachment(s)">
                                            <i class="bx bx-paperclip"></i> {{ $n->claim_attachments_count }}
                                        </span>
                                    @endif
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
                                <td class="text-muted small">
                                    {{ $n->created_at->format('d M Y') }}<br>
                                    <span class="text-muted" style="font-size:.7rem">{{ $n->created_at->format('H:i') }}</span>
                                </td>

                                <td>
                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($n->status) }}</span>
                                    @if ($isAdmin && $unregistered && $ageDays >= 2)
                                        <div class="small mt-1" style="font-size:.7rem;color:#b45309">
                                            {{ $ageDays }}d unregistered
                                        </div>
                                    @endif
                                </td>

                                {{-- Elite Claim Number column --}}
                                <td>
                                    @if ($n->elite_claim_no)
                                        <span class="fw-semibold text-success small">{{ $n->elite_claim_no }}</span>
                                    @elseif ($isAdmin)
                                        <form action="{{ route('claim.notifications.elite', $n) }}" method="POST"
                                              class="d-flex gap-1">
                                            @csrf
                                            <input type="text" name="elite_claim_no"
                                                   class="form-control form-control-sm"
                                                   style="min-width:110px"
                                                   placeholder="Enter Elite No."
                                                   required>
                                            <button type="submit" class="btn btn-sm btn-success"
                                                    title="Record &amp; notify claimant">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Pending</span>
                                    @endif
                                </td>

                                @if ($isAdmin)
                                    <td>
                                        <form action="{{ route('claim.notifications.status', $n) }}" method="POST"
                                              class="d-flex gap-1">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm"
                                                    style="min-width:95px">
                                                <option value="received"   {{ $n->status === 'received'   ? 'selected' : '' }}>Received</option>
                                                <option value="registered" {{ $n->status === 'registered' ? 'selected' : '' }}>Registered</option>
                                                <option value="closed"     {{ $n->status === 'closed'     ? 'selected' : '' }}>Closed</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    title="Update status">
                                                <i class="bx bx-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>

                            {{-- Expanded detail row --}}
                            <tr id="desc-{{ $n->id }}" class="table-light border-0" style="display:none">
                                <td colspan="{{ $isAdmin ? 9 : 5 }}" class="px-3 py-3">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <p class="mb-1 text-muted small fw-semibold text-uppercase">Description</p>
                                            <p class="mb-2" style="white-space:pre-wrap">{{ $n->description }}</p>
                                            <p class="mb-0 text-muted small">
                                                Policy period: {{ $n->policy_start->format('d M Y') }} – {{ $n->policy_end->format('d M Y') }}
                                                &nbsp;·&nbsp; Source: {{ ucfirst($n->policy_source) }}
                                                @if ($isAdmin && $n->user)
                                                    &nbsp;·&nbsp; Account:
                                                    <span class="badge {{ $n->user->account_status === 'active' ? 'bg-success' : ($n->user->account_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                        {{ ucfirst($n->user->account_status) }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>

                                        @if ($n->claim_attachments_count > 0)
                                            <div class="col-12 col-md-4">
                                                <p class="mb-1 text-muted small fw-semibold text-uppercase">Attachments</p>
                                                @foreach ($n->claimAttachments as $att)
                                                    <div class="mb-1">
                                                        <a href="{{ route('claim.attachment.download', $att) }}"
                                                           class="small text-decoration-none"
                                                           target="_blank">
                                                            <i class="bx bx-download me-1"></i>{{ $att->original_name }}
                                                            <span class="text-muted">({{ number_format($att->size / 1024, 0) }} KB)</span>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 9 : 5 }}" class="text-center text-muted py-5">
                                    @if ($isAdmin)
                                        No claim notifications found.
                                    @else
                                        You have not submitted any claim notifications yet.<br>
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

    <script>
        document.querySelectorAll('.ref-toggle').forEach(function (badge) {
            badge.addEventListener('click', function () {
                var targetId = badge.dataset.target;
                var row = document.getElementById(targetId);
                if (row) {
                    // Eager-load attachments list only on first expand
                    row.style.display = row.style.display === 'none' ? '' : 'none';
                }
            });
        });
    </script>

</x-layouts.app>
