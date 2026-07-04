<x-layouts.app>

<div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('broker.claims') }}" class="text-muted small">
        <i class="bx bx-arrow-back me-1"></i>My Claim Notifications
    </a>
</div>
<h4 class="fw-bold mb-4">Submit Claim Notification</h4>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:14px;max-width:720px;">
    <div class="card-body p-4">
        <form action="{{ route('broker.claims.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ── Policy ── --}}
            <h6 class="fw-bold mb-3" style="color:#B18752;border-bottom:1px solid #e5e9f2;padding-bottom:.5rem;">
                <i class="bx bx-file-blank me-1"></i>Policy
            </h6>

            @if ($policy)
                {{-- Pre-selected from policy page --}}
                <input type="hidden" name="policy_no" value="{{ $policy['policy_no'] }}">
                <div class="p-3 rounded-3 mb-4" style="background:#f8f9fc;border:1px solid #e5e9f2;">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="small text-muted">Policy Number</div>
                            <div class="fw-bold">{{ $policy['policy_no'] }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Product Type</div>
                            <div class="fw-semibold">{{ $policy['product_type'] }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Insured</div>
                            <div class="fw-semibold">{{ $policy['name'] }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted">Policy Period</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($policy['date_from'])->format('d M Y') }} –
                                {{ \Carbon\Carbon::parse($policy['date_to'])->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('broker.claims.create') }}" class="small text-muted mt-2 d-inline-block">
                        <i class="bx bx-swap me-1"></i>Change policy
                    </a>
                </div>
            @else
                {{-- Dropdown selector --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Policy <span class="text-danger">*</span></label>
                    @if (count($policies))
                        <select name="policy_no" class="form-select @error('policy_no') is-invalid @enderror" required
                                onchange="this.form.action='{{ route('broker.claims.create') }}?policy_no='+encodeURIComponent(this.value); this.form.method='GET'; this.form.submit();">
                            <option value="">— Select a policy —</option>
                            @foreach ($policies as $p)
                                <option value="{{ $p['policy_no'] }}" {{ old('policy_no') === $p['policy_no'] ? 'selected' : '' }}>
                                    {{ $p['policy_no'] }} — {{ $p['product_type'] }} ({{ $p['name'] }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Selecting a policy will load the insured details automatically.</div>
                    @else
                        <input type="text" name="policy_no" class="form-control @error('policy_no') is-invalid @enderror"
                               placeholder="e.g. SLM/MTR/2025/00123" value="{{ old('policy_no') }}" required>
                        <div class="form-text text-warning">Policy list unavailable — enter the policy number manually.</div>
                    @endif
                    @error('policy_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif

            {{-- ── Incident Details ── --}}
            <h6 class="fw-bold mb-3" style="color:#B18752;border-bottom:1px solid #e5e9f2;padding-bottom:.5rem;">
                <i class="bx bx-calendar-event me-1"></i>Incident Details
            </h6>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Date of Loss <span class="text-danger">*</span></label>
                    <input type="date" name="incident_date" class="form-control @error('incident_date') is-invalid @enderror"
                           value="{{ old('incident_date') }}" max="{{ date('Y-m-d') }}" required>
                    @error('incident_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Time of Loss <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="time" name="incident_time" class="form-control @error('incident_time') is-invalid @enderror"
                           value="{{ old('incident_time') }}">
                    @error('incident_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Location of Incident <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" name="incident_location" class="form-control @error('incident_location') is-invalid @enderror"
                       value="{{ old('incident_location') }}" maxlength="200" placeholder="e.g. Lagos Island, Victoria Bridge">
                @error('incident_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Description of Loss <span class="text-danger">*</span></label>
                <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror"
                          minlength="20" maxlength="3000" required
                          placeholder="Provide a clear description of the incident, nature of loss, and any relevant circumstances…">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text text-end"><span id="descCount">0</span> / 3000</div>
            </div>

            {{-- ── Claimant ── --}}
            <h6 class="fw-bold mb-3" style="color:#B18752;border-bottom:1px solid #e5e9f2;padding-bottom:.5rem;">
                <i class="bx bx-user me-1"></i>Claimant
            </h6>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_third_party" value="1"
                       id="thirdPartySwitch" role="switch"
                       {{ old('is_third_party') ? 'checked' : '' }}
                       onchange="toggleThirdParty(this.checked)">
                <label class="form-check-label" for="thirdPartySwitch">
                    Third-party claim
                    <span class="text-muted fw-normal small">(claimant is not the insured)</span>
                </label>
            </div>

            <div id="thirdPartyFields" style="display:{{ old('is_third_party') ? 'block' : 'none' }};">
                <div class="p-3 rounded-3 mb-3" style="background:#fff8ed;border:1px solid #fbd38d;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Claimant Name <span class="text-danger">*</span></label>
                            <input type="text" name="claimant_name" id="claimantName"
                                   class="form-control @error('claimant_name') is-invalid @enderror"
                                   value="{{ old('claimant_name') }}" maxlength="100"
                                   placeholder="Full name of the third-party claimant">
                            @error('claimant_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Claimant Phone <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="tel" name="claimant_phone"
                                   class="form-control @error('claimant_phone') is-invalid @enderror"
                                   value="{{ old('claimant_phone') }}" maxlength="20"
                                   placeholder="+234 XXX XXX XXXX">
                            @error('claimant_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Claimant Email <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="email" name="claimant_email"
                                   class="form-control @error('claimant_email') is-invalid @enderror"
                                   value="{{ old('claimant_email') }}" maxlength="150"
                                   placeholder="claimant@example.com">
                            @error('claimant_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div id="ownClaimNote" style="display:{{ old('is_third_party') ? 'none' : 'block' }};"
                 class="alert alert-light border small py-2 mb-3">
                <i class="bx bx-info-circle me-1 text-muted"></i>
                Claimant will be recorded as the insured on the policy.
            </div>

            {{-- ── Attachments ── --}}
            <h6 class="fw-bold mb-3" style="color:#B18752;border-bottom:1px solid #e5e9f2;padding-bottom:.5rem;">
                <i class="bx bx-paperclip me-1"></i>Supporting Documents <span class="text-muted fw-normal">(optional)</span>
            </h6>

            <div class="mb-4">
                <input type="file" name="attachments[]" id="attachments" class="form-control"
                       multiple accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Up to 5 files · PDF, JPG or PNG · Max 5 MB each</div>
                <div id="fileList" class="mt-2 small"></div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2">
                <a href="{{ route('broker.claims') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn flex-grow-1" style="background:#161616;color:#B18752;font-weight:700;">
                    <i class="bx bx-send me-1"></i>Submit Claim Notification
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function toggleThirdParty(on) {
    document.getElementById('thirdPartyFields').style.display = on ? 'block' : 'none';
    document.getElementById('ownClaimNote').style.display     = on ? 'none'  : 'block';
    const nameField = document.getElementById('claimantName');
    if (nameField) nameField.required = on;
}

const ta = document.querySelector('textarea[name="description"]');
const counter = document.getElementById('descCount');
if (ta) {
    ta.addEventListener('input', () => counter.textContent = ta.value.length);
    counter.textContent = ta.value.length;
}

document.getElementById('attachments')?.addEventListener('change', function () {
    const list = document.getElementById('fileList');
    list.innerHTML = Array.from(this.files).map(f =>
        `<span class="badge bg-light text-dark border me-1"><i class="bx bx-paperclip"></i> ${f.name} (${(f.size/1024).toFixed(0)} KB)</span>`
    ).join('');
});

// Initialise required state on load
toggleThirdParty(document.getElementById('thirdPartySwitch').checked);
</script>

</x-layouts.app>
