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

    @switch($response['status'])
        @case('error')
            <div class="alert alert-danger">
                <strong>Server Error:</strong> {{ $response['message'] ?? 'An unknown error occurred.' }}
            </div>
            @break

        @default
            @if ($isMultiple)

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
                                <strong>Claim No:</strong> {{ $single->claim_no }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Policy No:</strong> {{ $single->policy_no }}
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
                        <div class="mt-3">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                                Back to Search
                            </a>
                        </div>
                    </div>
                </div>

            @endif
            @break

    @endswitch

</div>

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
