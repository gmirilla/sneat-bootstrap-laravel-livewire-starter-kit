<x-layouts.guest>

<div class="container mt-5" style="max-width:520px">

    <div class="text-center mb-4">
        <h4 class="fw-bold">Retrieve Your Brown Card</h4>
        <p class="text-muted small">Look up your ECOWAS Brown Card certificate using your policy number or vehicle registration number.</p>
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
            <form action="{{ route('browncard.lookup.post') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Search By</label>
                    <select name="search_type" class="form-select">
                        <option value="policy_no" {{ old('search_type') === 'policy_no' ? 'selected' : '' }}>Policy Number</option>
                        <option value="reg_no" {{ old('search_type', 'reg_no') === 'reg_no' ? 'selected' : '' }}>Vehicle Registration Number</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Policy Number or Registration Number <span class="text-danger">*</span></label>
                    <input type="text" name="search_value" class="form-control @error('search_value') is-invalid @enderror"
                           value="{{ old('search_value') }}" placeholder="e.g. NIIP/LEAD/KAD/0321/0000679/TPP or QWQ111AQ" required autofocus>
                    @error('search_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Dashes and spaces in the registration number are ignored.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search-alt me-1"></i> Find My Brown Card
                </button>
            </form>
        </div>
    </div>

    @isset($result)
        <div class="card shadow-sm mt-4 border-success">
            <div class="card-body p-4">
                <h5 class="fw-bold text-success mb-3">
                    <i class="bx bx-check-shield me-1"></i> Certificate Found
                </h5>

                <table class="table table-sm table-borderless mb-3">
                    <tbody>
                        @if ($result['policyHolder'])
                            <tr><th class="text-muted" style="width:45%">Policy Holder</th><td>{{ $result['policyHolder'] }}</td></tr>
                        @endif
                        @if ($result['policyNumber'])
                            <tr><th class="text-muted">Policy Number</th><td>{{ $result['policyNumber'] }}</td></tr>
                        @endif
                        @if ($result['regNo'])
                            <tr><th class="text-muted">Registration Number</th><td>{{ $result['regNo'] }}</td></tr>
                        @endif
                        @if ($result['vehicleMake'] || $result['vehicleModel'])
                            <tr><th class="text-muted">Vehicle</th><td>{{ trim(($result['vehicleMake'] ?? '') . ' ' . ($result['vehicleModel'] ?? '')) }}</td></tr>
                        @endif
                        @if ($result['issueDate'])
                            <tr><th class="text-muted">Issue Date</th><td>{{ $result['issueDate'] }}</td></tr>
                        @endif
                        @if ($result['expiryDate'])
                            <tr><th class="text-muted">Expiry Date</th><td>{{ $result['expiryDate'] }}</td></tr>
                        @endif
                    </tbody>
                </table>

                @unless ($result['verifiedLive'])
                    <div class="alert alert-warning small mb-3">
                        This result is based on our latest records and could not be verified live against NIIP at this time.
                    </div>
                @endunless

                <a href="{{ $result['downloadUrl'] }}" class="btn btn-success w-100" target="_blank" rel="noopener">
                    <i class="bx bx-download me-1"></i> Download Certificate
                </a>
                <p class="text-muted small text-center mt-2 mb-0">This download link expires in 5 minutes.</p>
            </div>
        </div>
    @endisset

</div>

</x-layouts.guest>
