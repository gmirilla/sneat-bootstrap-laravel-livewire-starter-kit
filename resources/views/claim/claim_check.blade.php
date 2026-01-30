<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

@php
    $results = $response['data'];
@endphp
<div class="container mt-4">

@if ($results instanceof \Illuminate\Support\Collection && $results->count() > 1)

    {{-- MULTIPLE RESULTS --}}
    <div class="alert alert-info">
        Multiple claims found. Select a claim to view details.
    </div>

    <table class="table table-bordered table-striped mt-3">
        <thead>
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
                <tr>
                    <td>{{ $item->claim_no }}</td>
                    <td>{{ $item->policy_no }}</td>
                    <td>{{ ucfirst($item->state) }}</td>
                    <td>{{ $item->loss_date ? \Carbon\Carbon::parse($item->loss_date)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $item->notification_date ? \Carbon\Carbon::parse($item->notification_date)->format('M d, Y') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@else

    {{-- SINGLE RESULT (your existing detailed card) --}}
    @php
        $data = $results instanceof \Illuminate\Support\Collection ? $results->first() : $results;
    @endphp

    {{-- Your existing breadcrumb + claim summary card goes here --}}
    
    @include('claim.single_claim_view', ['data' => $data])

@endif
</div>
