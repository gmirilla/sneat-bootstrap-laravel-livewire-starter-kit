<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">My Claim Notifications</h4>
        <p class="text-muted small mb-0">All claim notifications you have submitted</p>
    </div>
    <a href="{{ route('broker.claims.create') }}" class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
        <i class="bx bx-plus me-1"></i>Submit Claim Notification
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Reference</th>
                    <th>Policy</th>
                    <th>Type</th>
                    <th>Claimant</th>
                    <th>Incident Date</th>
                    <th>Status</th>
                    <th>Elite Claim No</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($claims as $c)
                    @php
                        $statusColour = match($c->status) {
                            'received'   => 'bg-primary',
                            'registered' => 'bg-success',
                            'closed'     => 'bg-secondary',
                            default      => 'bg-light text-dark',
                        };
                    @endphp
                    <tr>
                        <td class="fw-semibold small">
                            {{ $c->reference_no }}
                            @if ($c->claimAttachments->count())
                                <span class="badge bg-light text-dark border ms-1" title="{{ $c->claimAttachments->count() }} attachment(s)">
                                    <i class="bx bx-paperclip"></i> {{ $c->claimAttachments->count() }}
                                </span>
                            @endif
                        </td>
                        <td class="small">{{ $c->policy_no }}</td>
                        <td class="small">{{ $c->policy_type }}</td>
                        <td class="small">
                            {{ $c->claimant_name }}
                            @if ($c->is_third_party)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">3P</span>
                            @endif
                        </td>
                        <td class="small">{{ $c->incident_date->format('d M Y') }}</td>
                        <td><span class="badge {{ $statusColour }}">{{ ucfirst($c->status) }}</span></td>
                        <td class="small fw-semibold {{ $c->elite_claim_no ? 'text-success' : 'text-muted' }}">
                            {{ $c->elite_claim_no ?? '—' }}
                        </td>
                        <td class="small text-muted">{{ $c->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            No claim notifications yet.
                            <a href="{{ route('broker.claims.create') }}">Submit your first notification</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($claims->hasPages())
        <div class="card-footer">{{ $claims->links() }}</div>
    @endif
</div>

</x-layouts.app>
