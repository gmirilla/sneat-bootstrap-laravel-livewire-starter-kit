<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
    

    <h3 class="mb-3">Claim Check Result</h3>
    @if (isset($response))

        {{-- Error or Not Found --}}
        @if ($response['status'] !== 'success' || empty($data))
            <div class="alert alert-warning">
                {{ $response['message'] }}
            </div>
                {{-- NO RESULTS FOUND --}}
    <div class="alert alert-warning">
        No claims found for the provided Policy Number.
    </div>


        @else
            @php

                // States grouped as processing
                $processingStates = ['draft', 'reserved', 'submitted'];

                // Determine current step
                $currentStep = match (true) {
                    in_array($data->state, $processingStates) => 'processing',
                    $data->state === 'approved' => 'approved',
                    $data->state === 'settled' => 'settled',
                    $data->state === 'refused' => 'refused',
                    default => 'submitted'
                };

                // Format dates
                $lossDate = $data->loss_date
                    ? \Carbon\Carbon::parse($data->loss_date)->format('M d, Y')
                    : 'N/A';

                $reportedDate = $data->notification_date
                    ? \Carbon\Carbon::parse($data->notification_date)->format('M d, Y')
                    : 'N/A';
            @endphp

            {{-- Breadcrumb Status Bar --}}
<nav aria-label="breadcrumb">
    <ol class="breadcrumb p-3 bg-light rounded shadow-sm">

        {{-- Submitted --}}
        <li class="breadcrumb-item d-flex align-items-center 
            {{ $currentStep === 'submitted' ? 'fw-bold text-primary' : ($currentStep !== 'submitted' ? 'text-success' : '') }}">
            <i class="bi bi-upload me-2"></i> Submitted
        </li>

        {{-- Processing --}}
        <li class="breadcrumb-item d-flex align-items-center 
            {{ $currentStep === 'processing' ? 'fw-bold text-primary' : ($currentStep === 'approved' || $currentStep === 'settled' ? 'text-success' : '') }}">
            <i class="bi bi-gear-wide-connected me-2"></i> Processing
        </li>

        {{-- Approved --}}
        <li class="breadcrumb-item d-flex align-items-center 
            {{ $currentStep === 'approved' ? 'fw-bold text-primary' : ($currentStep === 'settled' ? 'text-success' : '') }}">
            <i class="bi bi-check-circle me-2"></i> Approved
        </li>

        {{-- Settled --}}
        <li class="breadcrumb-item d-flex align-items-center 
            {{ $currentStep === 'settled' ? 'fw-bold text-primary text-success' : '' }}">
            <i class="bi bi-cash-coin me-2"></i> Settled
        </li>

        {{-- Rejected --}}
        <li class="breadcrumb-item d-flex align-items-center 
            {{ $currentStep === 'refused' ? 'fw-bold text-danger' : '' }}">
            <i class="bi bi-x-circle me-2"></i> Rejected
        </li>

    </ol>
</nav>

            {{-- Claim Summary Card --}}
            <div class="card mt-3 shadow-sm">
                <div class="card-header">
                    Claim Summary
                </div>

                <div class="card-body">

                    <p><strong>Claim Number:</strong> {{ $data->claim_no }}</p>

                    {{-- Status Text --}}
                    @switch(true)
                        @case(in_array($data->state, $processingStates))
                            <p><strong>Status:</strong> Claim Submitted (Processing)</p>
                            @break

                        @case($data->state === 'approved')
                            <p><strong>Status:</strong> <span class="text-success">Claim Approved</span> <i>Payment processing</i></p>
                            @break

                        @case($data->state === 'settled')
                            <p><strong>Status:</strong> <span class="text-success">Claim Settled</span></p>
                            @break

                        @case($data->state === 'refused')
                            <p><strong>Status:</strong> <span class="text-danger">Claim Rejected</span></p>
                            @break

                        @default
                            <p><strong>Status:</strong> {{ ucfirst($data->state) }}</p>
                    @endswitch

                    <p><strong>Claim Type:</strong> {{ $data->claim_type ?? 'N/A' }}</p>
                    <p><strong>Date of Loss:</strong> {{ $lossDate }}</p>
                    <p><strong>Reported Date:</strong> {{ $reportedDate }}</p>

                    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Claim Check</a>

                </div>
            </div>

        @endif

    @else
        <div class="alert alert-info">
            No claim data available.
        </div>
    @endif

</div>