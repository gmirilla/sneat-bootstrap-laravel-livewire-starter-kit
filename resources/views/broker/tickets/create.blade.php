<x-layouts.app>

<div class="d-flex align-items-center gap-2 mb-1">
    <a href="{{ route('broker.tickets') }}" class="text-muted small">
        <i class="bx bx-arrow-back me-1"></i>My Tickets
    </a>
</div>
<h4 class="fw-bold mb-4">Open a Support Ticket</h4>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:14px;max-width:680px;">
    <div class="card-body p-4">
        <form action="{{ route('broker.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Policy <span class="text-danger">*</span></label>
                @if (count($policies))
                    <select name="policy_no" class="form-select @error('policy_no') is-invalid @enderror" required>
                        <option value="">— Select a policy —</option>
                        @foreach ($policies as $p)
                            <option value="{{ $p['policy_no'] }}"
                                {{ old('policy_no', request('policy_no')) === $p['policy_no'] ? 'selected' : '' }}>
                                {{ $p['policy_no'] }} — {{ $p['product_type'] }} ({{ $p['name'] }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="policy_no" class="form-control @error('policy_no') is-invalid @enderror"
                           placeholder="e.g. SLM/MTR/2025/00123"
                           value="{{ old('policy_no', request('policy_no')) }}" required>
                    <div class="form-text text-warning">Policy list unavailable — enter the policy number manually.</div>
                @endif
                @error('policy_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                       value="{{ old('subject') }}" maxlength="200" required
                       placeholder="Brief description of your query">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                    <option value="normal"  {{ old('priority', 'normal') === 'normal'  ? 'selected' : '' }}>Normal</option>
                    <option value="high"    {{ old('priority')            === 'high'    ? 'selected' : '' }}>High</option>
                    <option value="urgent"  {{ old('priority')            === 'urgent'  ? 'selected' : '' }}>Urgent</option>
                </select>
                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                <textarea name="body" rows="6"
                          class="form-control @error('body') is-invalid @enderror"
                          placeholder="Describe your query in detail…"
                          minlength="10" maxlength="5000" required>{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text text-end"><span id="bodyCount">0</span> / 5000</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Attachments <span class="text-muted fw-normal">(optional)</span></label>
                <input type="file" name="attachments[]" id="attachments" class="form-control"
                       multiple accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Up to 5 files · PDF, JPG or PNG · Max 5 MB each</div>
                <div id="fileList" class="mt-2 small"></div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('broker.tickets') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn flex-grow-1" style="background:#161616;color:#B18752;font-weight:700;">
                    <i class="bx bx-send me-1"></i>Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const ta = document.querySelector('textarea[name="body"]');
const counter = document.getElementById('bodyCount');
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
</script>

</x-layouts.app>
