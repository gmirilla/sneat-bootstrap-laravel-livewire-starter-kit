<x-layouts.app>

<div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('broker.claims') }}" class="text-muted small">
        <i class="bx bx-arrow-back me-1"></i>My Claim Notifications
    </a>
</div>

<div class="card border-0 shadow-sm mx-auto mt-3" style="border-radius:14px;max-width:620px;">
    <div class="card-body p-5 text-center">

        <div class="mb-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:64px;height:64px;background:#161616;">
                <i class="bx bx-check" style="font-size:2rem;color:#B18752;"></i>
            </div>
            <h4 class="fw-bold mb-1">Claim Notification Submitted</h4>
            <p class="text-muted mb-0">Your notification has been received and is being reviewed.</p>
        </div>

        <div class="p-3 rounded-3 mb-4" style="background:#f8f9fc;border:1px solid #e5e9f2;">
            <div class="small text-muted mb-1">MySalam Reference Number</div>
            <div class="fw-bold" style="font-size:1.4rem;color:#161616;letter-spacing:.05em;">
                {{ $notification->reference_no }}
            </div>
            <div class="small text-muted mt-1">Please quote this reference in all correspondence</div>
        </div>

        <table class="table table-sm text-start mb-4" style="font-size:.9rem;">
            <tr>
                <td class="text-muted border-0 ps-0">Policy</td>
                <td class="fw-semibold border-0">{{ $notification->policy_no }}</td>
            </tr>
            <tr>
                <td class="text-muted ps-0">Product Type</td>
                <td class="fw-semibold">{{ $notification->policy_type }}</td>
            </tr>
            <tr>
                <td class="text-muted ps-0">Claimant</td>
                <td class="fw-semibold">
                    {{ $notification->claimant_name }}
                    @if ($notification->is_third_party)
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Third Party</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted ps-0">Date of Loss</td>
                <td class="fw-semibold">
                    {{ $notification->incident_date->format('d M Y') }}
                    @if ($notification->incident_time)
                        at {{ $notification->incident_time }}
                    @endif
                </td>
            </tr>
            @if ($notification->incident_location)
            <tr>
                <td class="text-muted ps-0">Location</td>
                <td class="fw-semibold">{{ $notification->incident_location }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-muted ps-0 border-0">Status</td>
                <td class="border-0">
                    <span class="badge bg-primary">{{ ucfirst($notification->status) }}</span>
                </td>
            </tr>
        </table>

        @if (!$notification->elite_claim_no)
            <div class="alert alert-info py-2 small text-start mb-4">
                <i class="bx bx-info-circle me-1"></i>
                An <strong>Elite Claim Number</strong> will be assigned by our claims team and will appear in
                <a href="{{ route('broker.claims') }}">My Claim Notifications</a> once registered.
            </div>
        @else
            <div class="alert alert-success py-2 small text-start mb-4">
                <i class="bx bx-check-circle me-1"></i>
                Elite Claim Number: <strong>{{ $notification->elite_claim_no }}</strong>
            </div>
        @endif

        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('broker.claims') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-list-ul me-1"></i>View All Claims
            </a>
            <a href="{{ route('broker.claims.create') }}" class="btn btn-sm" style="background:#161616;color:#B18752;font-weight:700;">
                <i class="bx bx-plus me-1"></i>Submit Another
            </a>
        </div>

    </div>
</div>

</x-layouts.app>
