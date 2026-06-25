<x-layouts.guest>

<div class="container mt-4" style="max-width:640px">

    <div class="text-center mb-4">
        <h4 class="fw-bold">Claim Notification</h4>
        <p class="text-muted small">Review your policy details below, then complete the notification form.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Read-only policy summary --}}
    <div class="card bg-light border-0 mb-4">
        <div class="card-body py-3">
            <p class="mb-1 small text-muted text-uppercase fw-semibold">Policy Details</p>
            <div class="row g-2 mt-1">
                <div class="col-sm-4">
                    <div class="small text-muted">Policy Number</div>
                    <div class="fw-semibold">{{ $lookup['policy_no'] }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted">Policy Type</div>
                    <div class="fw-semibold">{{ $lookup['policy_type'] }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted">Policy Period</div>
                    <div class="fw-semibold">
                        {{ \Carbon\Carbon::parse($lookup['start_date'])->format('d M Y') }}
                        &ndash;
                        {{ \Carbon\Carbon::parse($lookup['end_date'])->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('claim.notify.submit') }}" method="POST">
                @csrf

                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em">Your Details</h6>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="claimant_name"
                               class="form-control @error('claimant_name') is-invalid @enderror"
                               value="{{ old('claimant_name') }}" required>
                        @error('claimant_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="claimant_phone"
                               class="form-control @error('claimant_phone') is-invalid @enderror"
                               value="{{ old('claimant_phone') }}" required>
                        @error('claimant_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="claimant_email"
                           class="form-control @error('claimant_email') is-invalid @enderror"
                           value="{{ old('claimant_email') }}" required>
                    @error('claimant_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        The claims team will use this address to contact you. A MySalam account will be created if you don't already have one.
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.05em">Incident Details</h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Date of Incident <span class="text-danger">*</span></label>
                    <input type="date" name="incident_date"
                           class="form-control @error('incident_date') is-invalid @enderror"
                           value="{{ old('incident_date') }}"
                           max="{{ date('Y-m-d') }}" required>
                    @error('incident_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Description of Incident <span class="text-danger">*</span></label>
                    <textarea name="description" rows="5"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Describe what happened, where, and the nature of damage or loss…"
                              minlength="20" maxlength="3000" required>{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text text-end"><span id="descCount">0</span> / 3000</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('claim.notify.lookup') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bx bx-send me-1"></i> Submit Claim Notification
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    const desc    = document.querySelector('textarea[name="description"]');
    const counter = document.getElementById('descCount');
    if (desc) {
        desc.addEventListener('input', () => counter.textContent = desc.value.length);
        counter.textContent = desc.value.length;
    }
</script>
@endpush

</x-layouts.guest>
