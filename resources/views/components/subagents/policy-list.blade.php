<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    .pillgreen {
        background-color: #0d5800;
        border: none;
        color: rgb(255, 252, 252);
        padding: 5px 10px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        margin: 4px 2px;
        border-radius: 16px;
    }

    .pillinfo {
        background-color: red;
        border: none;
        color: rgb(255, 252, 252);
        padding: 5px 10px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        margin: 4px 2px;
        border-radius: 16px;
    }

    .pillyellow {
        background-color: rgb(202, 227, 9);
        border: none;
        color: rgb(255, 252, 252);
        padding: 5px 10px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        margin: 4px 2px;
        border-radius: 16px;
    }
</style>
<div class="card row gy-2 gx-3 align-items-center mb-3">
        <div class="sf-header">
        <i class="fa fa-sliders"></i> Filter Policies
    </div>

            <div class="card-body table-responsive">
                <table class="table table-striped table-sm" style="font-size:0.8rem" id='policylist'>
                    @if (empty($searchParams))

                        <thead>
                            <th class="col">Policy No.</th>
                            <th class="col">Policy Type</th>
                            <th class="col">Risk/REGNO </th>
                            <th class="col">Insured Name</th>
                            <th class="col">Contribution</th>
                            <th class="col">Created At</th>
                            <th class="col">Status</th>
                            
                            @if ($user->role=='agent'||$user->role=='superadmin')   
                            <th class="col">Agent</th>
                                
                            @endif
                            <th class="col">Action</th>
                        </thead>
                    @else
                        <thead>
                            <tr>
                                <th colspan="8" style="font-size:1.2 rem" class="text-center"><b>Filtered Results
                                        : </b>
                                    @if (!empty($searchParams['policytype']))
                                        <b>Policy Type: </b><i
                                            style="color:red">{{ ucwords($searchParams['policytype']) }}</i> ;
                                    @endif

                                    @if (!empty($searchParams['status']))
                                        <b>Status: </b> <i
                                            style="color:red">{{ ucwords($searchParams['status']) }}</i> ;
                                    @endif

                                    @if (!empty($searchParams['datefrom']))
                                        <b>Date From:</b> <i style="color:red">{{ $searchParams['datefrom'] }}</i>
                                    @endif

                                    @if (!empty($searchParams['dateto']))
                                        <b>To:</b> <i style="color:red">{{ $searchParams['dateto'] }}</i>
                                    @endif
                                </th>
                            </tr>
                            <tr>
                                <th class="col">Policy No.</th>
                                <th class="col">Policy Type</th>
                                <th class="col">Risk/REGNO </th>
                                <th class="col">Insured Name</th>
                                <th class="col">Contribution</th>
                                <th class="col">Created At</th>
                                <th class="col">Status</th>
                                @if ($user->role=='agent'||$user->role=='superadmin')  
                                   <th class="col">Agent</th> 
                                @endif
                                
                                <th class="col">Action</th>
                            </tr>
                        </thead>
                    @endif

                    <tfoot>
                        <tr>

                            <td colspan="8" style="font-size:1.2 rem" class="text-center"><b>Filtered Results
                                    : </b>
                                @if (!empty($searchParams['policytype']))
                                    <b>Policy Type: </b><i
                                        style="color:red">{{ ucwords($searchParams['policytype']) }}</i> ;
                                @endif

                                @if (!empty($searchParams['status']))
                                    <b>Status: </b> <i style="color:red">{{ ucwords($searchParams['status']) }}</i> ;
                                @endif

                                @if (!empty($searchParams['datefrom']))
                                    <b>Date From:</b> <i style="color:red">{{ $searchParams['datefrom'] }}</i>
                                @endif

                                @if (!empty($searchParams['dateto']))
                                    <b>To:</b> <i style="color:red">{{ $searchParams['dateto'] }}</i>
                                @endif
                            </td>
                        </tr>

                    </tfoot>

                    <tbody>
                        @forelse ($policies as $policy )
                            <tr>
                                <td>
                                    @if (empty($policy->policyno))
                                        Incomplete Policy
                                    @else
                                        {{ $policy->policyno }}
                                    @endif
                                </td>
                                <td>{{ $policy->producttype }}</td>
                                @if (empty($policy->getrisk()->regno))
                                    <td>No reg No. {{ $policy->id }}</td>
                                @else
                                    <td>{{ $policy->getrisk()->regno }}</td>
                                @endif

                                <td>{{ $policy->insured_name }}</td>
                                <td>{{ $policy->contribution }}</td>
                                <td>{{ $policy->created_at }}</td>
                                <td>
                                    @switch($policy->status)
                                        @case('approved')
                                            <span class="pill pillgreen"> {{ $policy->status }} </span> <br>
                                            @if (Str::contains(strtolower($policy->producttype), 'motor'))
                                                @php $niip = $policy->getniipstatus(); @endphp

                                                @if (is_array($niip) && ($niip['isSuccess'] ?? false) === true)
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal"
                                                        data-message="{{ $policy->niip_status }}">
                                                        <span class="pill pillgreen">NIIP Success</span>
                                                    </a><br>
                                                @elseif (is_array($niip) && ($niip['statusCode'] ?? '') === '11')
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal"
                                                        data-message="{{ $policy->niip_status }}">
                                                        <span class="pill pillyellow">Possible Issue</span>
                                                    </a><br>
                                                @else
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#dynamicModal"
                                                        data-message="{{ $policy->niip_status }}">
                                                        <span class="pill pillinfo">NIIP Issue</span>
                                                    </a><br>
                                                @endif
                                            @endif
                                        @break

                                        @case('draft')
                                            <span class="pill pilldraft"> {{ $policy->status }} </span> <br>
                                        @break

                                        @case('failed')
                                            <span class="pill pillinfo"> {{ $policy->status }} </span> <br>
                                        @break

                                        @default
                                            <span class="pill pillinfo"> {{ $policy->status }} </span> <br>
                                    @endswitch

                                </td>
                                @if ($user->role=='agent'||$user->role=='superadmin')  
                                   <td>{{ $policy->getagentname() }}</td>

                                @endif
                                <td>
                                    <form action="{{ route('view_policy') }}" method="get">
                                        <input type="number" value="{{ $policy->id }}" hidden name='id'>

                                        <button class="btn btn-primary" style="font-size:0.75rem" type="submit">View
                                            Policy</button>

                                    </form>

                                    @if ($policy->status == 'approved' && !empty($policy->policyno))
                                        <br>
                                        <a target="_blank" class="btn btn-success" style="font-size:0.75rem"
                                            href="http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no={{ $policy->policyno }}">
                                            Certificate
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty

                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
            <script>
            new DataTable('#policylist', {
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
