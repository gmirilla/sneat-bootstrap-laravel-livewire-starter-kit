<x-layouts.guest>

<div class="container mt-5" style="max-width:560px">

    <div class="card shadow-sm border-0">
        <div class="card-body p-5 text-center">

            <div class="mb-3">
                <i class="bx bx-check-circle" style="font-size:3.5rem;color:#16a34a"></i>
            </div>

            <h4 class="fw-bold mb-1">Claim Notification Submitted</h4>
            <p class="text-muted">Your notification has been sent to our claims team.</p>

            <div class="bg-light rounded-3 p-3 my-4">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Your Reference Number</div>
                <div class="fw-bold" style="font-size:1.4rem;letter-spacing:.05em;color:#1a56db">
                    {{ $notification->reference_no }}
                </div>
            </div>

            <table class="table table-sm text-start mb-4">
                <tr>
                    <td class="text-muted fw-semibold" style="width:40%">Policy</td>
                    <td>{{ $notification->policy_no }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold">Type</td>
                    <td>{{ $notification->policy_type }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold">Incident Date</td>
                    <td>{{ $notification->incident_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-semibold">Contact Email</td>
                    <td>{{ $notification->claimant_email }}</td>
                </tr>
            </table>

            @if ($accountCreated)
                <div class="alert alert-info text-start">
                    <strong>Account Created</strong><br>
                    A MySalam account has been created for you. An administrator will verify your identity and
                    you will receive an email with login instructions once your account is activated.
                </div>
            @endif

            <p class="text-muted small">
                Our claims team will contact you at <strong>{{ $notification->claimant_email }}</strong>.
                Please quote your reference number in all correspondence.
            </p>

            <a href="{{ route('home') }}" class="btn btn-outline-primary mt-2">
                <i class="bx bx-home me-1"></i> Return to Home
            </a>

        </div>
    </div>

</div>

</x-layouts.guest>
