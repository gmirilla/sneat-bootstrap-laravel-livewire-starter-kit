<x-layouts.app>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Elite Broker &amp; Agent Portfolio</h4>
        <p class="text-muted small mb-0">Partner records sourced live from the Elite ERP.</p>
    </div>
    <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()" id="refreshBtn">
        <i class="bx bx-refresh me-1"></i>Refresh
    </button>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error') || $errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bx bx-error-circle me-2"></i>
        @if ($errors->any())
            {{ $errors->first() }}
        @else
            {{ session('error') }}
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($error)
    <div class="alert alert-danger">
        <i class="bx bx-error-circle me-2"></i>{{ $error }}
    </div>
@endif

{{-- ── Broker / Agent table ── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <strong>Brokers &amp; Agents</strong>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" id="brokerSearch" class="form-control form-control-sm"
                   placeholder="Search name, email…" style="width:220px"
                   oninput="applyFilter()">
            <select id="typeFilter" class="form-select form-select-sm" style="width:130px"
                    onchange="applyFilter()">
                <option value="">All types</option>
                <option value="broker">Broker</option>
                <option value="agent">Agent</option>
            </select>
            <select id="perPageSelect" class="form-select form-select-sm" style="width:90px"
                    onchange="changePerPage(this.value)">
                <option value="25" selected>25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="brokerTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Website</th>
                        <th class="text-center">MySalam Account</th>
                        <th class="text-center">Policies</th>
                    </tr>
                </thead>
                <tbody id="brokerBody">
                    @forelse ($brokers as $b)
                        @php
                            $account      = $brokerAccounts->get($b['broker_id']);
                            $eliteEmail   = $b['email'] ?? '';
                            $elitePhone   = $b['phone'] ?? '';
                            $isValidEmail = $eliteEmail && strtolower($eliteEmail) !== 'tba' && filter_var($eliteEmail, FILTER_VALIDATE_EMAIL);
                        @endphp
                        <tr class="broker-row"
                            data-id="{{ $b['broker_id'] }}"
                            data-name="{{ strtolower($b['name']) }}"
                            data-email="{{ strtolower($eliteEmail) }}"
                            data-type="{{ $b['cust_type'] }}"
                            style="cursor:pointer;display:none"
                            onclick="loadPolicies({{ $b['broker_id'] }}, '{{ addslashes($b['name']) }}', this)">
                            <td class="text-muted small">{{ $b['broker_id'] }}</td>
                            <td class="fw-semibold">{{ $b['name'] }}</td>
                            <td>
                                <span class="badge {{ $b['cust_type'] === 'broker' ? 'bg-primary' : 'bg-success' }}">
                                    {{ ucfirst($b['cust_type']) }}
                                </span>
                            </td>
                            <td class="small">{{ $eliteEmail ?: '—' }}</td>
                            <td class="small">{{ $elitePhone ?: '—' }}</td>
                            <td class="small">
                                @if (!empty($b['website']))
                                    <a href="{{ $b['website'] }}" target="_blank" rel="noopener"
                                       class="text-decoration-none" onclick="event.stopPropagation()">
                                        <i class="bx bx-link-external me-1"></i>{{ parse_url($b['website'], PHP_URL_HOST) }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- MySalam Account column --}}
                            <td class="text-center" onclick="event.stopPropagation()">
                                @if ($account)
                                    <span class="badge {{ $account->account_status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}"
                                          title="{{ $account->email }}">
                                        <i class="bx bx-user-check me-1"></i>{{ ucfirst($account->account_status) }}
                                    </span>
                                @else
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary py-0"
                                            style="font-size:.75rem"
                                            onclick="openCreateModal(
                                                {{ $b['broker_id'] }},
                                                '{{ addslashes($b['name']) }}',
                                                '{{ addslashes($isValidEmail ? $eliteEmail : '') }}',
                                                '{{ addslashes($elitePhone) }}'
                                            )">
                                        <i class="bx bx-user-plus me-1"></i>Create
                                    </button>
                                @endif
                            </td>

                            <td class="text-center">
                                <i class="bx bx-chevron-right text-muted" id="arrow-{{ $b['broker_id'] }}"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                @if (!$error) No broker or agent records found in Elite. @endif
                            </td>
                        </tr>
                    @endforelse

                    {{-- shown when a filter matches nothing --}}
                    <tr id="noResultsRow" style="display:none">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bx bx-search-alt me-1"></i>No records match your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- footer: count info + Bootstrap pagination --}}
    @if (count($brokers) > 0)
    <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="text-muted small" id="brokerCount"></span>
        <nav aria-label="Broker pagination">
            <ul class="pagination pagination-sm mb-0" id="brokerPagination"></ul>
        </nav>
    </div>
    @endif
</div>

{{-- ── Policy panel ── --}}
<div id="policyPanel" style="display:none">
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <strong id="policyPanelTitle">Policies</strong>
                <span id="policyPanelSub" class="text-muted small ms-2"></span>
            </div>
            <button class="btn btn-sm btn-outline-secondary" onclick="closePolicyPanel()">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <div id="policyLoading" class="card-body text-center py-5" style="display:none">
            <div class="spinner-border text-secondary" role="status"></div>
            <p class="text-muted mt-2 mb-0">Loading policies…</p>
        </div>

        <div id="policyError" class="card-body" style="display:none">
            <div class="alert alert-danger mb-0" id="policyErrorMsg"></div>
        </div>

        <div id="policyTableWrap" class="card-body p-0" style="display:none">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Policy No</th>
                            <th>Insured</th>
                            <th>Product</th>
                            <th>From</th>
                            <th>To</th>
                            <th class="text-end">Sum Insured</th>
                            <th class="text-end">Gross Premium</th>
                        </tr>
                    </thead>
                    <tbody id="policyBody"></tbody>
                </table>
            </div>
        </div>

        <div id="policyFooter" class="card-footer text-muted small" style="display:none">
            <span id="policyCount">0</span> polic(ies) &nbsp;·&nbsp;
            Total SI: <strong id="totalSI"></strong> &nbsp;·&nbsp;
            Total Premium: <strong id="totalPremium"></strong>
        </div>
    </div>
</div>

<script>
const POLICIES_URL = '{{ route("elite.broker.policies") }}';
const CSRF         = '{{ csrf_token() }}';

// ── State ──────────────────────────────────────────────────────────────────
let allBrokers      = [];   // all broker <tr> elements
let filteredBrokers = [];   // subset after search/type filter
let currentPage     = 1;
let perPage         = 25;

let activeBrokerRow = null;
let activeBrokerId  = null;

// ── Boot ──────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    allBrokers = Array.from(document.querySelectorAll('.broker-row'));
    applyFilter();
});

// ── Filter ────────────────────────────────────────────────────────────────
function applyFilter() {
    const needle = document.getElementById('brokerSearch').value.trim().toLowerCase();
    const type   = document.getElementById('typeFilter').value.toLowerCase();

    filteredBrokers = allBrokers.filter(function (row) {
        const textMatch = row.dataset.name.includes(needle) || row.dataset.email.includes(needle);
        const typeMatch = !type || row.dataset.type === type;
        return textMatch && typeMatch;
    });

    currentPage = 1;
    renderPage();
}

function changePerPage(n) {
    perPage     = parseInt(n, 10);
    currentPage = 1;
    renderPage();
}

// ── Pagination render ─────────────────────────────────────────────────────
function renderPage() {
    const total      = filteredBrokers.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    currentPage      = Math.min(currentPage, totalPages);

    const start      = (currentPage - 1) * perPage;
    const end        = Math.min(start + perPage, total);
    const pageSet    = new Set(filteredBrokers.slice(start, end));

    // Show / hide rows
    allBrokers.forEach(function (row) {
        row.style.display = pageSet.has(row) ? '' : 'none';
    });

    // No-results row
    document.getElementById('noResultsRow').style.display = total === 0 ? '' : 'none';

    // Count label
    const countEl = document.getElementById('brokerCount');
    if (countEl) {
        countEl.textContent = total === 0
            ? 'No records match your search'
            : 'Showing ' + (start + 1) + '–' + end + ' of ' + total + ' record(s)';
    }

    // Pagination controls
    const pagEl = document.getElementById('brokerPagination');
    if (pagEl) pagEl.innerHTML = buildPagination(totalPages);
}

function goToPage(n) {
    currentPage = n;
    renderPage();
    // Scroll table into view only if below the fold
    const rect = document.getElementById('brokerTable').getBoundingClientRect();
    if (rect.top < 0) {
        document.getElementById('brokerTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ── Build Bootstrap 5 pagination HTML ────────────────────────────────────
function buildPagination(totalPages) {
    if (totalPages <= 1) return '';

    // Pages to show: always first, last, current ±2, with ellipsis gaps
    const pages = new Set();
    pages.add(1);
    pages.add(totalPages);
    for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
        pages.add(i);
    }
    const sorted = Array.from(pages).sort(function (a, b) { return a - b; });

    let html = '';

    // Prev
    html += li(currentPage === 1, false, '&laquo;', 'goToPage(' + (currentPage - 1) + ')', 'Previous');

    let prev = 0;
    sorted.forEach(function (p) {
        if (p - prev > 1) {
            html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        html += li(false, p === currentPage, p, 'goToPage(' + p + ')', p);
        prev = p;
    });

    // Next
    html += li(currentPage === totalPages, false, '&raquo;', 'goToPage(' + (currentPage + 1) + ')', 'Next');

    return html;
}

function li(disabled, active, label, onclick, ariaLabel) {
    const cls = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
    const tag = disabled || active ? 'span' : 'a';
    const attrs = tag === 'a'
        ? ' href="#" onclick="event.preventDefault();' + onclick + '"'
        : '';
    return '<li class="' + cls + '">'
         + '<' + tag + ' class="page-link"' + attrs + ' aria-label="' + ariaLabel + '">'
         + label
         + '</' + tag + '></li>';
}

// ── Policy panel ──────────────────────────────────────────────────────────
function loadPolicies(brokerId, brokerName, rowEl) {
    if (activeBrokerId === brokerId && document.getElementById('policyPanel').style.display !== 'none') {
        closePolicyPanel();
        return;
    }

    if (activeBrokerRow) {
        activeBrokerRow.classList.remove('table-active');
        const prev = document.getElementById('arrow-' + activeBrokerId);
        if (prev) prev.className = 'bx bx-chevron-right text-muted';
    }
    rowEl.classList.add('table-active');
    activeBrokerRow = rowEl;
    activeBrokerId  = brokerId;

    const arrow = document.getElementById('arrow-' + brokerId);
    if (arrow) arrow.className = 'bx bx-chevron-down text-primary';

    document.getElementById('policyPanel').style.display     = '';
    document.getElementById('policyLoading').style.display   = '';
    document.getElementById('policyError').style.display     = 'none';
    document.getElementById('policyTableWrap').style.display = 'none';
    document.getElementById('policyFooter').style.display    = 'none';
    document.getElementById('policyPanelTitle').textContent  = brokerName;
    document.getElementById('policyPanelSub').textContent    = '';

    document.getElementById('policyPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });

    fetch(POLICIES_URL + '?broker_id=' + brokerId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
    })
    .then(function (res) { return res.json(); })
    .then(function (json) {
        document.getElementById('policyLoading').style.display = 'none';
        if (json.status !== 'success') {
            document.getElementById('policyErrorMsg').textContent = json.message || 'Failed to load policies.';
            document.getElementById('policyError').style.display  = '';
            return;
        }
        renderPolicies(json.data || []);
    })
    .catch(function (err) {
        document.getElementById('policyLoading').style.display = 'none';
        document.getElementById('policyErrorMsg').textContent  = 'Network error: ' + err.message;
        document.getElementById('policyError').style.display   = '';
    });
}

function renderPolicies(policies) {
    const tbody = document.getElementById('policyBody');

    if (!policies.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No policies found for this broker.</td></tr>';
        document.getElementById('policyTableWrap').style.display = '';
        document.getElementById('policyPanelSub').textContent    = '0 policies';
        return;
    }

    const fmt = new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    let totalSI = 0, totalPrem = 0;

    tbody.innerHTML = policies.map(function (p) {
        totalSI   += parseFloat(p.actual_si_lc             || 0);
        totalPrem += parseFloat(p.actual_gross_premium_lc  || 0);
        return '<tr>'
            + '<td class="fw-semibold small">'    + escHtml(p.policy_no)      + '</td>'
            + '<td class="small">'                + escHtml(p.name)           + '</td>'
            + '<td class="small">'                + escHtml(p.product_type)   + '</td>'
            + '<td class="small text-muted">'     + formatDate(p.date_from)   + '</td>'
            + '<td class="small text-muted">'     + formatDate(p.date_to)     + '</td>'
            + '<td class="text-end small">₦'      + fmt.format(p.actual_si_lc)              + '</td>'
            + '<td class="text-end small">₦'      + fmt.format(p.actual_gross_premium_lc)   + '</td>'
            + '</tr>';
    }).join('');

    document.getElementById('policyTableWrap').style.display = '';
    document.getElementById('policyFooter').style.display    = '';
    document.getElementById('policyCount').textContent       = policies.length;
    document.getElementById('totalSI').textContent           = '₦' + fmt.format(totalSI);
    document.getElementById('totalPremium').textContent      = '₦' + fmt.format(totalPrem);
    document.getElementById('policyPanelSub').textContent    = policies.length + ' polic' + (policies.length === 1 ? 'y' : 'ies');
}

function closePolicyPanel() {
    document.getElementById('policyPanel').style.display = 'none';
    if (activeBrokerRow) {
        activeBrokerRow.classList.remove('table-active');
        const arrow = document.getElementById('arrow-' + activeBrokerId);
        if (arrow) arrow.className = 'bx bx-chevron-right text-muted';
    }
    activeBrokerRow = null;
    activeBrokerId  = null;
}

// ── Utilities ─────────────────────────────────────────────────────────────
function escHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatDate(str) {
    if (!str) return '—';
    const d = new Date(str);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── Create Broker Account modal ───────────────────────────────────────────
function openCreateModal(brokerId, name, email, phone) {
    document.getElementById('modal_broker_id').value = brokerId;
    document.getElementById('modal_name').value      = name;
    document.getElementById('modal_email').value     = email;
    document.getElementById('modal_phone').value     = phone;
    document.getElementById('modal_broker_label').textContent = name + ' (ID #' + brokerId + ')';
    document.getElementById('modal_send_reset').checked = !!email;

    // Warn if email is missing — user must fill it in
    const emailField = document.getElementById('modal_email');
    emailField.classList.toggle('border-warning', !email);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('createBrokerModal')).show();
}
</script>

{{-- ── Create Broker Account Modal ── --}}
<div class="modal fade" id="createBrokerModal" tabindex="-1" aria-labelledby="createBrokerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#161616;">
                <h5 class="modal-title fw-bold" style="color:#B18752;">
                    <i class="bx bx-user-plus me-2"></i>Create Broker Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('elite.broker.create-account') }}" method="POST">
                @csrf
                <input type="hidden" name="broker_id" id="modal_broker_id">

                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Creating a MySalam account for:
                        <strong id="modal_broker_label"></strong>
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modal_name"
                               class="form-control @error('name') is-invalid @enderror"
                               required maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="modal_email"
                               class="form-control @error('email') is-invalid @enderror"
                               required maxlength="150">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Must be a valid email — used for login and password reset.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" id="modal_phone"
                               class="form-control" maxlength="30">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="send_reset" id="modal_send_reset" value="1">
                        <label class="form-check-label" for="modal_send_reset">
                            Send password setup email to broker
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#161616;color:#B18752;font-weight:700;">
                        <i class="bx bx-user-plus me-1"></i>Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-layouts.app>
