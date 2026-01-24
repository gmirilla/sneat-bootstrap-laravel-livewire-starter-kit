<x-layouts.app>
    <script src="https://js.paystack.co/v2/inline.js"> </script>
    @php
        if ($policy->status == 'draft' or $policy->status == 'failed') {
            # code...
            $statcheck = 'enabled';
        } else {
            # code...
            $statcheck = 'disabled';
        }
        $user = auth()->user();
    @endphp


    <div class="col-12 col-md col-xl col-sm py-md-3 pl-md-5t  fs-6, fs-md-5, fs-lg-5, fs-xl-1">
        <form action="{{ route('submit_mpolicy') }}" method="post">
            @csrf
            <div class="card  mb-3">
                <div class="card-header">
                    <h4>POLICY DETAILS</h4>
                    <h5 style="color:#040273 "><b>POLICY NO :</b>
                        @if (!empty($policy->policyno))
                            {{ $policy->policyno }}
                        @else
                            Policy Number not yet Generated
                        @endif
                        <br>
                        @if ($policy->status == 'failed')
                            <span class="text-danger mr-5">
                                <b>STATUS : </b>{{ strtoupper($policy->status) }}
                            </span>
                        @else
                            <span class="mr-5">
                                <b>STATUS : </b>{{ strtoupper($policy->status) }}

                            </span>
                        @endif
                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button"
                            aria-expanded="false" aria-controls="collapseExample">
                            View
                        </a>
                        @if ($policy->status == 'approved')
                            <a target="_blank" class="btn btn-success ml-3"
                                href="http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no={{ $policy->policyno }}">Certificate</a>
                        @endif
                    </h5>

                    <div class="collapse" id="collapseExample">
                        <div class="card card-body">
                            @if (!empty($policy->elite_msg))
                                {{ $policy->elite_msg }}
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
                            <input class="form-control form-control-lg" type="text" name='policyid' hidden
                                value="{{ $policy->id }}">
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
                            <input class='form-control form-control-lg' type="text" disabled name="name"
                                id="name" value="{{ $policy->insured_name }}">
                        </div>
                    </div>

                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <h4>POLICY DETAILS</h4>
                    @if ($policy->status=='approved')
                    <button type="button" class="btn btn-primary">Make Claim</button>
                        
                    @endif
                </div>
                <div class="card-body">
                    <div class="flex row gy-2 gx-3 align-items-center mb-3">
                        <div class="col-lg-3 col-md-6">
                            <label class='form-label' for="regno">Plan Name</label>
                            <input class='form-control form-control-lg' type="text" name="regno" disabled
                                id="regno" value="{{ $policyrisk->regno }}">

                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class='form-label' for="chassisno">Maturity Date</label>
                            <input class='form-control form-control-lg' type="text" name="chassisno" disabled
                                id="chassisno" value="{{ $policyrisk->chassisno }}">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class='form-label' for="frequency">Contribution Frequency</label>
                            <input class='form-control form-control-lg' type="text" name="frequency" disabled
                                id="frequency" value="{{ $policy->frequency }}">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class='form-label' for="contribution">Contribution Amount</label>
                            <input class='form-control form-control-lg' type="text" name="contribution" disabled
                                id="contribution" value="{{ $policyrisk->contribution }}">
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
                    <h4>CONTRIBUTION(S) <a href="{{ route('init_paystack', $policy) }}"
                            class="btn btn-sm btn-primary float-end">Test</a></h4>
                    <button class="mt-4 p-1 btn btn-danger" data-bs-toggle="modal" type="button"
                        data-bs-target="#dynamicModal" data-bs-policy="{{ $policy->id }}"
                        data-bs-message="  {{ $policy->policyno }}"> Make Contribution</button>
                </div>
                <div class="table-responsive m-3">
                    <span><b>Number of Successful Contributions: </b>{{ count($policy->getsuccesspayments()) }}</span>
                    <br />
                    <span class="ml-5"><b>Contributions Made: </b>
                        &#8358;{{ number_format($policy->getsuccesspayments()->sum('amount'), 2) }}</span>
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
                                    <td colspan="4" class="text-center">No Contribution(s) on record.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    @if ($policy->status == 'draft' or $policy->status == 'failed')
        <div class="card  mb-3">
            <div class="card-header">
                <h4>DECLARATION</h4>
            </div>
            <div class="card-body">
                <div class="row gy-2 gx-3 align-items-center mb-3">
                    <div>
                        <input class="form-check-input" type="checkbox" required name="declaration"
                            id="declaration">

                        <label class="form-check-label" for="declaration">
                            I declare that I have read the privacy information on the use of personal data and confirm
                            that the information above is correct to the best of my knowledge
                            I also consent to the processing of my personal data in accordance with the Company's
                            Privacy Policy
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
                    <h5 class="modal-title" id="dynamicModalLabel">Make New Contribution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('pay_policy')}}" id="submitPolicy" name="submitPolicy" method="post">
                    @csrf
                    <div class="modal-body" id="modalMessage">

                    

                    </div>
                    <input type="hidden" name="policyid" id="policy_id" value="">
                    <input type="hidden" name="accesscode" id="accesscode" value="">
                    <div class="modal-footer">
                         <button onclick="paywithpaystack(event)" class="btn btn-primary" type="button" name="paystack" 
                         data-toggle="tooltip" data-placement="right" title="Pay Using Paystack">Paystack</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            var modal = document.getElementById('dynamicModal');

            modal.addEventListener('show.bs.modal', function(event) {

                // Button that triggered the modal
                var button = event.relatedTarget;

                // Extract data from the button
                var policyId = button.getAttribute('data-bs-policy');

                // Put the policy ID into the hidden input
                document.getElementById('policy_id').value = policyId;

                // Make AJAX call
                $.ajax({
                    url: '{{ route('init_paystack', ':policyId') }}'.replace(':policyId',
                        policyId),
                    method: 'GET',
                    success: function(response) {
                        let formatted = Number(response.contribution).toLocaleString('en-NG', {
                            style: 'currency',
                            currency: 'NGN'
                        });

                        document.getElementById('modalMessage').innerHTML =
                            'Make New Contribution of ' + formatted + ' for Policy No: ' +
                            response.policyno;
                        document.getElementById('accesscode').value = response.accesscode;

                    },
                    error: function(xhr) {
                        let message = "An unexpected error occurred.";

                        // If server returned JSON with a message
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        // If server returned plain text
                        else if (xhr.responseText) {
                            message = xhr.responseText;
                        }

                        document.getElementById('modalMessage').innerHTML =
                            `<p class="text-danger">${message}</p>`;
                    }
                });
            });
        });
    </script>
    <script>
function disableButton() {
    var button = document.getElementById("acreditbtn");
    button.disabled = true;
    button.innerText = "Processing...";
}

function paywithpaystack(event) {
  event.preventDefault(); // Prevent form submission

  const access_code = document.getElementById('accesscode').value;
  const popup = new PaystackPop();

  popup.resumeTransaction(access_code, {
    onCancel: () => {
      console.log("User cancelled");
      handlePaystackClose();
    },
   onSuccess: (transaction) => {
  const form = document.getElementById('submitPolicy');

  // Create a hidden input to hold the Paystack transaction data
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'paystack';
  input.value = JSON.stringify(transaction);
  form.appendChild(input);

  // Submit the form normally
  form.submit();
},
    onError: (error) => {
      console.log("Error: ", error.message);
    }
  });
}


function handlePaystackClose(){
    //Verify payment status 
    console.log("Handling Paystack close event");
    alert("Paystack window closed");
}

function paystacksuccess(e){
const transaction=e;
const form=document.getElementById('submitPolicy');
const formData=new FormData(form);
formData.append('paystackreference', transaction);

console.log("Form data to be Sent: ", ...formData.entries());
}
</script>
</x-layouts.app>
