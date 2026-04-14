<x-layouts.app>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="fs-6 fs-md-5 fs-lg-5 fs-xl-1">

<div class="card shadow-sm mb-4 mt-3">
    <div class="card-body">

        <div class="row text-center">

            <!-- Sub Agents -->
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded bg-light h-100">
                    <i class="fa fa-users text-primary mb-2" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2">No. of Sub Agents</h5>
                    <h4 class="fw-bold mt-2">{{$totalsubagents ?? 'N/A'}}</h4>
                    <p class="text-muted mb-0">Total registered under this agent</p>
                </div>
            </div>

            <!-- Active Sub Agents -->
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded bg-light h-100">
                    <i class="fa fa-check text-success mb-2" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2">Active Sub Agents</h5>
                    <h4 class="fw-bold mt-2">{{$activesubagents ?? 'N/A'}}</h4>
                    <p class="text-muted mb-0">Currently active and selling</p>
                </div>
            </div>

            <!-- Policies Sold -->
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded bg-light h-100">
                    <i class="fa fa-file text-warning mb-2" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2">Total Policies </h5>
                    <h4 class="fw-bold mt-2">{{$totalpolcount ?? 'N/A'}}</h4>
                    <p class="text-muted mb-0">Total policies Count</p>
                </div>
            </div>

        </div>

    </div>
</div>

    {{-- ── Per-Subagent Sales Summary ─────────────────────────────────────
    --}}
    @php
        $byAgent = $policies->groupBy('agent_id');

        $statusColour = [
            'approved'  => 'success',
            'draft'     => 'secondary',
            'failed'    => 'danger',
            'cancelled' => 'warning',
        ];
    @endphp

    @if ($byAgent->isNotEmpty())
        @if (!empty($searchParams) && array_filter($searchParams))
            <div class="alert alert-info py-2 mb-3" style="font-size:.82rem;">
                <i class="fa fa-filter me-1"></i>
                Summary reflects current filter —
                @if (!empty($searchParams['policytype'])) <strong>Type:</strong> {{ ucwords($searchParams['policytype']) }};  @endif
                @if (!empty($searchParams['status']))     <strong>Status:</strong> {{ ucwords($searchParams['status']) }};    @endif
                @if (!empty($searchParams['datefrom']))   <strong>From:</strong> {{ $searchParams['datefrom'] }};             @endif
                @if (!empty($searchParams['dateto']))     <strong>To:</strong> {{ $searchParams['dateto'] }};                 @endif
            </div>
        @endif

        <div class="row g-3 mb-4">
            @foreach ($byAgent as $agentId => $agentPolicies)
                @php
                    $agentName    = $agentPolicies->first()->getagentname() ?? 'Unknown Agent';
                    $total        = $agentPolicies->count();
                    $byStatus     = $agentPolicies->groupBy('status');
                    $byType       = $agentPolicies->groupBy('producttype');
                    $totalPremium = $agentPolicies->sum('contribution');
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="font-size:.85rem;">
                                <i class="fa fa-user me-1"></i> {{ $agentName }}
                            </span>
                            <span class="badge bg-light text-primary">{{ $total }} {{ Str::plural('policy', $total) }}</span>
                        </div>
                        <div class="card-body py-2 px-3">

                            {{-- Status breakdown --}}
                            <p class="text-muted mb-1" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.06em;">By Status</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach ($byStatus as $status => $statusPolicies)
                                    <span class="badge bg-{{ $statusColour[$status] ?? 'secondary' }}">
                                        {{ ucfirst($status) }}: {{ $statusPolicies->count() }}
                                    </span>
                                @endforeach
                            </div>

                            {{-- Policy type breakdown --}}
                            <p class="text-muted mb-1" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.06em;">By Policy Type</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach ($byType as $type => $typePolicies)
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                        {{ ucwords($type) }}: {{ $typePolicies->count() }}
                                    </span>
                                @endforeach
                            </div>

                            {{-- Total premium --}}
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                                <small class="text-muted">Total Contribution</small>
                                <span class="fw-bold text-success" style="font-size:.85rem;">
                                    &#8358;{{ number_format($totalPremium, 2) }}
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    
    <x-subagents.subagentfilters
        :products="$products"
        :user="$user"
        :agentslist="$agentslist"
        :searchParams="$searchParams"
    />

    <div class="mb-4 card-body table-responsive">
            <div class="sf-header mb-4">
        <i class="fa fa-sliders"></i> Agent Sales Summary
    </div>
        <table class="table table-striped table-hover align-middle" id="agentsummary" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th>Subagent Name</th>
                    <th>Product Type</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($byAgent as $agentId => $policysummary)
                
                    <tr>
                        <td>{{ $policysummary->first()->getagentname() }}</td>
                        <td>{{ ucwords($policysummary->first()->producttype) }}</td>
                        <td>{{ $policysummary->count() }}</td>
                    </tr>

                
            @empty
            <tr>
                <td colspan="3" class="text-center text-muted py-4">
                    <i class="fa fa-info-circle me-1"></i> No policies found for the current filters.
                </td>  
            </tr>
                
            @endforelse
                </tbody>
        </table>
    </div>



    <x-subagents.policy-list
        :user="$user"
        :searchParams="$searchParams"
        :policies="$policies"
    />

</div>
<script>
            new DataTable('#agentsummary', {
                dom: 'Bfrtip', // Adds the button controls
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        title: 'Policy List',

                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export to PDF',
                        title: 'Policy List',
                        orientation: 'landscape', // optional
                        pageSize: 'A4' // optional
                    }
                ]
            });
        </script>






</x-layouts.app>
