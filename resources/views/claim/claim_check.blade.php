<x-layouts.guest>
@php
    $results = $response['data'];
@endphp

<div class="container mt-5">
    @dd($results)

    @if ($results instanceof \Illuminate\Support\Collection && $results->count() > 1)

        {{-- MULTIPLE RESULTS --}}
        <div class="alert alert-info rounded-3 shadow-sm">
            <strong>Notice:</strong> Multiple claims found for this policy. Please select a claim to view details.
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
                    @foreach ($results as $item)
                        <tr class="cursor-pointer hover-shadow" >
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
        @php
            $data = $results instanceof \Illuminate\Support\Collection ? $results->first() : $results;
        @endphp

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
                        <strong>Claim No:</strong> {{ $data->claim_no }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Policy No:</strong> {{ $data->policy_no }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $data->state === 'approved' ? 'success' : ($data->state === 'pending' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($data->state) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Date of Loss:</strong> {{ $data->loss_date ? \Carbon\Carbon::parse($data->loss_date)->format('M d, Y') : 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Reported Date:</strong> {{ $data->notification_date ? \Carbon\Carbon::parse($data->notification_date)->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                        Back to Search
                    </a>
                </div>
            </div>
        </div>

    @endif

</div>

<style>
    /* Hover effect for table rows */
    .hover-shadow:hover {
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    /* Responsive table rounded edges */
    .table-responsive table {
        border-radius: 0.5rem;
        overflow: hidden;
    }
</style>

</x-layouts.guest>