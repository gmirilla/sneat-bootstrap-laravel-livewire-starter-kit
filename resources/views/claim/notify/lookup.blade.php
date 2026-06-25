<x-layouts.guest>

<div class="container mt-5" style="max-width:480px">

    <div class="text-center mb-4">
        <h4 class="fw-bold">Submit a Claim Notification</h4>
        <p class="text-muted small">Enter your policy number and the phone number registered on the policy to continue.</p>
    </div>

    @if (session('lookup_error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('lookup_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('claim.notify.lookup') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Policy Number <span class="text-danger">*</span></label>
                    <input type="text" name="policy_no" class="form-control @error('policy_no') is-invalid @enderror"
                           value="{{ old('policy_no') }}" placeholder="e.g. SLM/MTR/2025/00123" required autofocus>
                    @error('policy_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Registered Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}" placeholder="e.g. 08012345678" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Must match the phone number on your policy.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search-alt me-1"></i> Find My Policy
                </button>
            </form>
        </div>
    </div>

    <p class="text-center text-muted small mt-3">
        Already submitted a claim? <a href="{{ route('home') }}#claimcheck">Check claim status</a>
    </p>

</div>

</x-layouts.guest>
