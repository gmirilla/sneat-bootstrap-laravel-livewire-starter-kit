<x-layouts.app>
    @php
        if ($policy->status=='draft'or $policy->status=='failed') {
            # code...
            $statcheck='enabled';
        } else {
            # code...
            $statcheck='disabled';
        }
        $user = auth()->user();
    @endphp


<div class="col-12 col-md col-xl col-sm py-md-3 pl-md-5t  fs-6, fs-md-5, fs-lg-5, fs-xl-1">
<form action="{{route('submit_mpolicy')}}" method="post">
    @csrf
    <div class="card  mb-3">
        <div class="card-header"><h4>POLICY DETAILS</h4>
        <h5 style="color:#040273 "><b>POLICY NO  :</b> 
            @if (!empty($policy->policyno))
                {{$policy->policyno}}
            @else
                Policy Number not yet Generated
            @endif
        <br>
        @if ($policy->status=='failed')
            <span class="text-danger mr-5">
                <b>STATUS : </b>{{strtoupper($policy->status)}} 
            </span>       
            
        @else
        <span class="mr-5">
            <b>STATUS : </b>{{strtoupper($policy->status)}}

        </span>         
            
        @endif
        <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
    View
  </a>
  @if ($policy->status=='approved')
  <a target="_blank" class="btn btn-success ml-3" href="http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no={{$policy->policyno}}">Certificate</a>
  @endif
        </h5>
  
        <div class="collapse" id="collapseExample">
  <div class="card card-body">
    @if (!empty($policy->elite_msg))
    {{$policy->elite_msg}}     
    @else
     N/A   
    @endif
  </div>
</div>
        </div>
                                      <div class="card-body">
                                    <div class="row gy-2 gx-3 align-items-center mb-3">
                                        <div class="col-auto">
                                            <label for="" class="form-label">PRODUCT</label>
                                            <input class="form-control form-control-lg" type="text" disabled
                                                value="{{ $policy->producttype }}">
                                        </div>
                                        <div class="col-auto">
                                            <label for="" class="form-label">PRODUCT TYPE</label>
                                            <input class="form-control form-control-lg" type="text" disabled
                                                value="{{ $policy->producttype }}">
                                            <input class="form-control form-control-lg" type="text" name='policyid'
                                                hidden value="{{ $policy->id }}">
                                        </div>
                                        <div class="col-auto">
                                            <label for="datefrom" class="form-label">DATE FROM</label>
                                            <input class="form-control form-control-lg" type="text" disabled
                                                value="{{ $policy->start_date }}">
                                        </div>
                                        <div class="col-auto">
                                            <label for="" class="form-label">DATE TO</label>
                                            <input class="form-control form-control-lg" type="text" disabled
                                                value="{{ $policy->end_date }}">

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card  mb-3">
                                <div class="card-header">
                                    <h4>PERSONAL DETAILS</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row gy-2 gx-3 align-items-center mb-3">
                                        <div class="col-auto">
                                            <label class='form-label' for="fname">Name on Certificate</label>
                                            <input class='form-control form-control-lg' type="text" disabled
                                                name="name" id="name" value="{{ $policy->insured_name }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4>POLICY DETAILS</h4>
                                </div>
                                <div class="card-body">
                                    <div class="flex row gy-2 gx-3 align-items-center mb-3">
                                        <div class="col-lg-3 col-md-6">
                                            <label class='form-label' for="regno">Plan Name</label>
                                            <input class='form-control form-control-lg' type="text" name="regno"
                                                disabled id="regno" value="{{ $policyrisk->regno }}">

                                        </div>

                                        <div class="col-lg-3 col-md-6">
                                            <label class='form-label' for="chassisno">Maturity Date</label>
                                            <input class='form-control form-control-lg' type="text" name="chassisno"
                                                disabled id="chassisno" value="{{ $policyrisk->chassisno }}">
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <label class='form-label' for="frequency">Contribution Frequency</label>
                                            <input class='form-control form-control-lg' type="text" name="frequency"
                                                disabled id="frequency" value="{{ $policy->frequency }}">
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <label class='form-label' for="contribution">Contribution Amount</label>
                                            <input class='form-control form-control-lg' type="text"
                                                name="contribution" disabled id="contribution"
                                                value="{{ $policyrisk->contribution }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card  mb-3">
                                <div class="card-header">
                                    <h4>BENEFICIARY(S)</h4>
                                </div>
                                <div class="table-responsive m-3">
                                    
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Relationship</th>
                                                <th>Percentage (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($policy->getbeneficiaries() as $beneficiary)
                                                <tr>
                                                    <td>{{ $beneficiary->name }}</td>
                                                    <td>{{ $beneficiary->relationship }}</td>
                                                    <td>{{ $beneficiary->percentage }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No beneficiaries on record.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                                        <div class="card  mb-3">
                                <div class="card-header">
                                    <h4>CONTRIBUTION(S) <a href="{{ route('init_paystack', $policy) }}" class="btn btn-sm btn-primary float-end">Test</a></h4>
                                                                            <button class="mt-4 p-1 btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#dynamicModal" data-bs-toDelete="{{ $policy }}"

                                            data-bs-message=" for Policy No: {{ $policy->policyno }}"> Make Contribution</button>  
                                </div>
                                <div class="table-responsive m-3">
                                    <span><b>Number of Successful Contributions: </b>{{count($policy->getsuccesspayments())}}</span> <br/>
                                    <span class="ml-5"><b>Contributions Made: </b> &#8358;{{number_format($policy->getsuccesspayments()->sum('amount'), 2)}}</span>
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tran Ref:</th>
                                                <th>Amount</th>
                                                <th>Status (%)</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($policy->getpayments() as $contribution)
                                                <tr>
                                                    <td>{{ $contribution->ref_id }}</td>
                                                    <td>{{ $contribution->amount }}</td>
                                                    <td>{{ $contribution->status }}</td>
                                                    <td>{{ $contribution->created_at }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No Contribution on record.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                    </div>
    @if ($policy->status=='draft' or $policy->status=='failed')
        
     <div class="card  mb-3">
        <div class="card-header"><h4>DECLARATION</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                <div>
                <input class="form-check-input" type="checkbox"  required name="declaration" id="declaration">

                    <label class="form-check-label" for="declaration">
                I declare that I have read the privacy information on the use of personal data and confirm that the information above is correct to the best of my knowledge
I also consent to the processing of my personal data in accordance with the Company's Privacy Policy
                </div>

            </div>
        </div>
        <div class="d-flex flex-row-reverse bd-highlight">
            <div class="p-2 bd-highlight" style="margin-right: 5px">
                <button class="btn btn-primary" type="submit">Submit Policy</button>

            </div>
            
        </div>
     </div>
         @endif
     </form>

</div>
    <!-- Dynamic Modal -->
    <div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dynamicModalLabel">Policy Renewal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="#" method="post">
                    @csrf
                    <input type="number" name="policy_id" id="policy_id" hidden >
                    <div class="modal-body" id="modalMessage">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Yes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script>

function newContribution(params) {
    console.log('I got WHere');
    let e = document.getElementById('state');
    console.log(e);
    var value = e.value;
    var text = e.options[e.selectedIndex].text;
        $.ajax({
            url: '/init_paystack/' + value,
            type: 'GET',
            success: function(lgas) {
                $('#lgas').html('');
                lgas.forEach(function(lga) {
                    $('#lgas').append('<option value="' + lga.lgaid + '">' + lga.lganame + '</option>');
                });
            }
        });

}


</script>
</x-layouts.app>

