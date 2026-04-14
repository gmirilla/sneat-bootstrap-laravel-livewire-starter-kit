<x-layouts.guest>
@php
    $results = $response['data'];
    // API returns associative arrays — normalise to objects for consistent -> access
    // Multiple results: $results is a list  [ [...], [...] ]
    // Single result:   $results is one item { ... }
    $isMultiple = is_array($results) && isset($results[0]);
    $items      = $isMultiple ? array_map(fn($r) => (object) $r, $results) : [];
    $single     = $isMultiple ? null : (object) $results;

@endphp

<div class="container mt-5">

    {{-- Flash messages --}}
    @if(session('enquiry_success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('enquiry_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('enquiry_error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('enquiry_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @switch($response['status'])
        @case('error')
            <div class="alert alert-danger">
                <strong>Server Error:</strong> {{ $response['message'] ?? 'An unknown error occurred.' }}
            </div>
            @break
        @case('not_found')
             <div class="alert alert-warning">
                <strong>No Claims Found:</strong> We couldn't find any claims matching the provided details. Please verify your information and try again.  If you believe this is an error, feel free to contact our claims team for assistance.
            </div>
            @break

        @default
            @if ($isMultiple)

                {{-- MULTIPLE RESULTS --}}
                <div class="alert alert-info rounded-3 shadow-sm">
                    <strong>Notice:</strong> Multiple claims found for this policy. Please select a claim to view details.
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#claimEnquiryModal">
                        <i class="bx bx-envelope me-1"></i> Contact Claims Team
                    </button>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover align-middle shadow-sm rounded-3">
                        <thead class="table-light">
                            <tr>
                                <th>Claim No</th>
                                <th>Policy No</th>
                                <th>Status</th>
                                <th>Date of Loss</th>
                                <th>Reported Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="cursor-pointer hover-shadow">
                                    <td class="fw-bold">{{ $item->claim_no }}</td>
                                    <td>{{ $item->policy_no }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->state === 'approved' ? 'success' : ($item->state === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($item->state) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->loss_date ? \Carbon\Carbon::parse($item->loss_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $item->notification_date ? \Carbon\Carbon::parse($item->notification_date)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else

                {{-- SINGLE RESULT --}}
                {{-- Breadcrumb + Claim Summary Card --}}
                <nav aria-label="breadcrumb" class="mt-3">
                    <ol class="breadcrumb bg-light rounded-3 p-2 shadow-sm">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Claim Details</li>
                    </ol>
                </nav>

                <div class="card shadow-sm rounded-3 mt-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Claim Summary</h5>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-2">
                                <strong>Claim No:</strong> {{ $single->claim_no ??  'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Policy No:</strong> {{ $single->policy_no ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $single->state === 'approved' ? 'success' : ($single->state === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($single->state) }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Date of Loss:</strong> {{ $single->loss_date ? \Carbon\Carbon::parse($single->loss_date)->format('M d, Y') : 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Reported Date:</strong> {{ $single->notification_date ? \Carbon\Carbon::parse($single->notification_date)->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                                Back to Search
                            </a>
                            <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#claimEnquiryModal"
                                data-claim="{{ $single->claim_no }}"
                                data-policy="{{ $single->policy_no }}">
                                <i class="bx bx-envelope me-1"></i> Contact Claims Team
                            </button>
                        </div>
                    </div>
                </div>

            @endif
            @break

    @endswitch

</div>

{{-- ── Claim Enquiry Modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="claimEnquiryModal" tabindex="-1" aria-labelledby="claimEnquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claimEnquiryModalLabel">
                    <i class="bx bx-envelope me-1"></i> Contact Claims Team
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('claim.send_enquiry') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="sender_name" class="form-control"
                                value="{{ old('sender_name', Auth::user()->name ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Your Email <span class="text-danger">*</span></label>
                            <input type="email" name="sender_email" class="form-control"
                                value="{{ old('sender_email', Auth::user()->email ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Claim No</label>
                            <input type="text" name="claim_no" id="modal_claim_no" class="form-control"
                                value="{{ old('claim_no') }}" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Policy No</label>
                            <input type="text" name="policy_no" id="modal_policy_no" class="form-control"
                                value="{{ old('policy_no') }}" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea name="message_body" class="form-control" rows="5"
                                placeholder="Describe your enquiry…" required maxlength="2000">{{ old('message_body') }}</textarea>
                            <div class="form-text text-end"><span id="charCount">0</span> / 2000</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Supporting Documents</label>
                            <input type="file" name="documents[]" id="documentInput"
                                class="form-control" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">Up to 5 files · Max 5 MB each · PDF, Word, JPG, PNG</div>
                            <ul id="fileList" class="list-unstyled mt-2 mb-0 small text-muted"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-send me-1"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Pre-fill claim/policy fields when opened from a specific row
    document.getElementById('claimEnquiryModal').addEventListener('show.bs.modal', function (e) {
        const trigger = e.relatedTarget;
        if (trigger) {
            const claim  = trigger.getAttribute('data-claim');
            const policy = trigger.getAttribute('data-policy');
            if (claim)  document.getElementById('modal_claim_no').value  = claim;
            if (policy) document.getElementById('modal_policy_no').value = policy;
        }
    });

    // Character counter
    const msgArea  = document.querySelector('textarea[name="message_body"]');
    const counter  = document.getElementById('charCount');
    if (msgArea) {
        msgArea.addEventListener('input', () => counter.textContent = msgArea.value.length);
    }

    // File list preview + client-side validation
    const docInput = document.getElementById('documentInput');
    const fileList = document.getElementById('fileList');
    if (docInput) {
        docInput.addEventListener('change', function () {
            fileList.innerHTML = '';
            const files  = Array.from(this.files);
            const maxMB  = 5;
            const maxFiles = 5;
            let valid = true;

            if (files.length > maxFiles) {
                fileList.innerHTML = `<li class="text-danger">Maximum ${maxFiles} files allowed.</li>`;
                this.value = '';
                return;
            }

            files.forEach(f => {
                const sizeMB = (f.size / 1024 / 1024).toFixed(2);
                const tooBig = f.size > maxMB * 1024 * 1024;
                if (tooBig) valid = false;
                fileList.insertAdjacentHTML('beforeend',
                    `<li class="${tooBig ? 'text-danger' : 'text-success'}">
                        <i class="bx ${tooBig ? 'bx-x-circle' : 'bx-check-circle'} me-1"></i>
                        ${f.name} <span class="text-muted">(${sizeMB} MB)${tooBig ? ' — exceeds 5 MB limit' : ''}</span>
                    </li>`
                );
            });

            if (!valid) this.value = '';
        });
    }
</script>

<style>
    .hover-shadow:hover {
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .table-responsive table {
        border-radius: 0.5rem;
        overflow: hidden;
    }
</style>

</x-layouts.guest>
