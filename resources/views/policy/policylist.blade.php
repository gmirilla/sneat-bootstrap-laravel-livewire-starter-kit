<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"></link>
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
        <div class="card-header">BUY A MOTOR POLICY</div>
        <div class="text-danger text-center">PURCHASE OF THIRD PARTY INSURANCE FOR TRUCKS, LORRIES AND ARTICULATED VEHICLES IS NOT ALLOWED VIA THIS APP</div>

        <form action="{{route('buy_policy')}}" method="get">
        <div class="card-body">
            <div class="d-flex flex-row">
                <div class="card-body">
                    <div>
                     <icon class="fa fa-car" style="font-size: 4rem;"></icon>
                            <p>Private Vehicles </p></div>
                        <button class="btn btn-primary mb-3" name="btnprivatemotor"style="font-size:0.8rem">Private Motor Third Party</button>
                </div>
                <div class="card-body">
                    <div><icon class="fa fa-truck" style="font-size: 4rem;"></icon>
                            <p>Taxis,Staff Bus, Mini Bus </p>
                        </div>
                          <button class="btn btn-primary mb-3" name="btncommercialmotor" style="font-size:0.8rem">Commercial Motor Third Party</button>
                </div>
                <div class="card-body">
                    <div>
                        <div><icon class="fa fa-motorcycle" style="font-size: 4rem;"></icon></div>
                    
                            <p>
                                Motorcycle, Tricycle
                            </p>
                        </div>
                        <button class="btn btn-primary mb-3" name="btnmotorcycle" style="font-size:0.8rem">Motorcycle/Tricycle Third Party</button>
                </div>
            </div>
        </div>
                </form>

        </div>

        <div class="card row gy-2 gx-3 align-items-center mb-3">
            <div class="card-body table-responsive">
                <table class="table table-striped table-sm" style="font-size:0.8rem" id='policylist'>
                    <thead>
                        <th class="col">Policy No.</th>
                        <th class="col">Policy Type</th>
                        <th class="col">Risk/REGNO </th>
                        <th class="col">Insured Name</th>
                        <th class="col">Contribution</th>
                        <th class="col">Status</th>
                        <th class="col">Action</th>
                    </thead>
                    <tbody>
                        @forelse ($policies as $policy ) 
                            <tr>
                                <td>@if (empty($policy->policyno))
                                    Incomplete Policy
                                @else
                                    {{$policy->policyno}}
                                @endif
                                    </td>
                                <td>{{$policy->producttype}}</td>
                                @if (empty($policy->getrisk()->regno))
                                   <td>No reg No. {{$policy->id}}</td> 
                                @else
                                    <td>{{$policy->getrisk()->regno}}</td>
                                @endif
                                
                                <td>{{$policy->insured_name}}</td>
                                <td>{{$policy->contribution}}</td>
                                <td>{{$policy->status}}</td>
                                <td><form action="{{route('view_policy')}}" method="get">
                                    <input type="number" value="{{$policy->id}}" hidden name='id'>

                                    <button class="btn btn-primary"  style="font-size:0.75rem"  type="submit">View Policy</button>

                                </form>
                                    
                                    @if ($policy->status=='approved' && !empty($policy->policyno))
                                    <br>
                                        <a target="_blank" class="btn btn-success"  style="font-size:0.75rem"  href="http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no={{$policy->policyno}}">
                                        Certificate
                                        </a>
                                    @endif

                                    Echo out NIIP msg {{$policy->niip_status}}
                                    
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
<script>
  new DataTable('#policylist');
</script>
</x-layouts.app>