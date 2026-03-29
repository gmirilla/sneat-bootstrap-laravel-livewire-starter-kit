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
<x-layouts.app>


    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-md col-xl col-sm py-md-3 pl-md-5t  fs-6, fs-md-5, fs-lg-5, fs-xl-1">

        <div class="card row gy-2 gx-3 align-items-center mb-3 mt-3 " style="font-size: 1rem;">
            <div class="card-header">BUY A SALAM POLICY</div>
            <div class="text-danger text-center">PURCHASE OF THIRD PARTY INSURANCE FOR TRUCKS, LORRIES AND ARTICULATED
                VEHICLES IS NOT ALLOWED VIA THIS APP</div>

            <form action="{{ route('buy_policy') }}" method="get">
                <div class="card-body">
                    <div class="flex row gy-2 gx-3 align-items-center mb-3">
                        <div class="card-body col-lg-3">
                            <div>
                                <icon class="fa fa-car" style="font-size: 4rem;"></icon>
                                <p>Private Vehicles </p>
                            </div>
                            <button class="btn btn-primary mb-3" name="btnprivatemotor"style="font-size:0.8rem">Private
                                Motor Third Party</button>
                        </div>
                        <div class="card-body col-lg-3">
                            <div>
                                <icon class="fa fa-truck" style="font-size: 4rem;"></icon>
                                <p>Taxis,Staff Bus, Mini Bus </p>
                            </div>
                            <button class="btn btn-primary mb-3" name="btncommercialmotor"
                                style="font-size:0.8rem">Commercial Motor Third Party</button>
                        </div>
                        <div class="card-body col-lg-3">
                            <div>
                                <div>
                                    <icon class="fa fa-motorcycle" style="font-size: 4rem;"></icon>
                                </div>

                                <p>
                                    Motorcycle, Tricycle
                                </p>
                            </div>
                            <button class="btn btn-primary mb-3" name="btnmotorcycle"
                                style="font-size:0.8rem">Motorcycle/Tricycle Third Party</button>
                        </div>
                                                <div class="card-body col-lg-3">
                            <div>
                                <icon class="fa fa-line-chart" style="font-size: 4rem;"></icon>
                                <p style="color: red">Occupier's Liability Policy**</p>
                            </div>
                            <button class="btn btn-primary mb-3" name="btnoccupier"style="font-size:0.8rem">Occupier's Liability</button>
                        </div>
                        <div class="card-body col-lg-3">
                            <div>
                                <icon class="fa fa-line-chart" style="font-size: 4rem;"></icon>
                                <p style="color: red">Salam Savings Policy  **</p>
                            </div>
                            <button class="btn btn-primary mb-3" name="btnsipp"style="font-size:0.8rem" disabled>Salam Investment
                                Plan</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <!--Filter for policy list -->
        <div class="card row gy-2 gx-3 align-items-center mb-3">
            <form action="{{ route('filterreport') }}" method="post">
                @csrf
                <div class="d-flex flex-row">
                    <div class="card-body">
                        <div>
                            <label for="policytype" class="form-label">Policy Type</label>
                            <select name="policytype" id="policytype" class="form-select">
                                @if ($searchParams['policytype'] ?? false)
                                    <option value="{{ $searchParams['policytype'] }}" selected>
                                        {{ ucwords($searchParams['policytype']) }}</option>
                                @else
                                    <option value="">--Select Policy Type--</option>
                                    @forelse ($products as $product)
                                        <option value="{{ $product }}">{{ ucwords($product) }}</option>
                                    @empty
                                    @endforelse

                                @endif

                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <label for="status" class="form-label">Policy Status</label>
                            <select name="status" id="status" class="form-select">
                                @if ($searchParams['status'] ?? false)
                                    <option value="{{ $searchParams['status'] }}" selected>
                                        {{ ucwords($searchParams['status']) }}</option>
                                @else
                                    <option value="">--Select Status--</option>
                                    <option value="approved">Approved</option>
                                    <option value="draft">Draft</option>
                                    <option value="failed">Failed</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <label for="status" class="form-label">Date From</label>
                            @if ($searchParams['datefrom'] ?? false)
                                <input type="date" name="datefromfilter" id="datefromfilter" class="form-control"
                                    value="{{ $searchParams['datefrom'] }}" disabled>
                                <input type="date" name="datefrom" id="datefrom" class="form-control"
                                    value="{{ $searchParams['datefrom'] }}" hidden>
                            @else
                                <input type="date" name="datefrom" id="datefrom" class="form-control">
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <label for="status" class="form-label">Date To</label>
                            @if ($searchParams['dateto'] ?? false)
                                <input type="date" name="datetofilter" id="datetofilter" class="form-control"
                                    value="{{ $searchParams['dateto'] }}" disabled>
                                <input type="date" name="dateto" id="dateto" class="form-control"
                                    value="{{ $searchParams['dateto'] }}" hidden>
                            @else
                                <input type="date" name="dateto" id="dateto" class="form-control">
                            @endif

                        </div>
                    </div>
                    @if ($user->role=='admin'||$user->role=='superadmin')
                    <div class="card-body">
                        <div>
                            <label for="agent" class="form-label">Agent</label>
                            <select name="agentcode" id="agentcode" class="form-select">

                                    <option value="">--Select Agent--</option>

                                    @forelse ($agentslist as $agent)
                                        <option value="{{ $agent->uid }}">{{ $agent->getuserinfo()->name ?? 'Unknown'}}</option>
                                    @empty
                                    @endforelse
                                    </select>
                        </div>
                    </div>
                        
                    @endif
                    <div class="card-body align-self-end">
                        <div>
                            <button class="btn btn-primary mb-3" type="submit" style="font-size:0.8rem">Apply
                                Filters</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card row gy-2 gx-3 align-items-center mb-3">
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
                            
                            @if ($user->role=='admin'||$user->role=='superadmin')   
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

                                    @if (!empty($searchParams['agentcode']))
                                        <b>Agent:</b> <i style="color:red">{{ $searchParams['agentcode'] }}</i>
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
                                @if ($user->role=='admin'||$user->role=='superadmin')  
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
                                @if ($user->role=='admin'||$user->role=='superadmin')  
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
        </div>
        </div>
        <!-- Dynamic Modal -->
        <div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dynamicModalLabel">Message</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modalMessage">
                        <!-- Message goes here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            const modal = document.getElementById('dynamicModal');
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const message = button.getAttribute('data-message');
                const modalBody = modal.querySelector('#modalMessage');
                modalBody.textContent = message;
            });
        </script>

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


    </x-layouts.app>
