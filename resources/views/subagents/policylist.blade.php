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
                    <h5 class="fw-bold mt-2">Policies Sold</h5>
                    <h4 class="fw-bold mt-2">{{$totalpolcount ?? 'N/A'}}</h4>
                    <p class="text-muted mb-0">Total policies Count</p>
                </div>
            </div>

        </div>

    </div>
</div>

    <x-subagents.subagentfilters  
        :products="$products"
        :user="$user"
        :agentslist="$agentslist"
        ::searchParams="$searchParams"
    />

    <x-subagents.policy-list
        :user="$user"
        :searchParams="$searchParams"
        :policies="$policies"
    />

</div>




</x-layouts.app>
