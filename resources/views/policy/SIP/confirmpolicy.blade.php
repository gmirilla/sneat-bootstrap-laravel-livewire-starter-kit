                @php
                    use App\Models\agentsdetailsModel;
                    Auth::check();
                    $usercheck = Auth::user();
                    $agent = agentsdetailsModel::where('uid', $usercheck->id)->first();
                    $creditleft = $agent->noallocated - $agent->noused;

                @endphp
                <style>
                    .modal {
                        display: none;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.5);
                    }

                    .modal-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background: white;
                        padding: 20px;
                        border-radius: 5px;
                    }
                </style>
                <script src="https://js.paystack.co/v2/inline.js"></script>
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
                    <div>
                        <div class="card  mb-3">
                            <div class="card-header">
                                <h4>CONFIRM POLICY DETAILS BELOW</h4>
                            </div>
                        </div>
                        <form id="submitPolicy" action="{{ route('pay_policy') }}" method="post">
                            @csrf
                            <div class="card  mb-3">
                                <div class="card-header">
                                    <h4>PRODUCT DETAILS</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row gy-2 gx-3 align-items-center mb-3">
                                        <div class="col-auto">
                                            <label for="" class="form-label">PRODUCT</label>
                                            <input class="form-control form-control-lg" type="text" disabled
                                                value={{ $policy->producttype }}>
                                        </div>
                                        <div class="col-auto">
                                            <label for="" class="form-label">PRODUCT TYPE</label>
                                            <input class="form-control form-control-lg" type="text" disabled
                                                value={{ $policy->producttype }}>
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
                    </div>


                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>SELECT PAYMENT METHOD</h4>
                        </div>
                        <div class="card-body">
                            <input type="text" name='policyid' hidden value="{{ $policy->id }}">
                            <div class="row gy-2 gx-3 align-items-center mb-3">
                                <div class="d-flex flex-row-reverse bd-highlight">
                                    <div class="p-2 bd-highlight" style="margin-right: 5px">
                                        @if ($accesscode != null)
                                            <button onclick="paywithpaystack(event)" class="btn btn-primary"
                                                type="button" name="paystack" data-acode="{{ $accesscode }}"
                                                data-toggle="tooltip" data-placement="right"
                                                title="Pay Using Paystack">Paystack</button>
                                        @endif

                                    </div>
                                    <div class="p-2 bd-highlight" style="margin-right: 5px">
                                        <button class="btn btn-primary" type="submit" disabled name="moniepoint"
                                            data-toggle="tooltip"
                                            data-placement="right"title="Coming Soon">Moniepoint</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    </form>
                    </div>




                    <script>
                        function disableButton() {
                            var button = document.getElementById("acreditbtn");
                            button.disabled = true;
                            button.innerText = "Processing...";
                        }

                        function paywithpaystack(event) {
                            event.preventDefault(); // Prevent form submission

                            const access_code = event.currentTarget.getAttribute('data-acode');
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


                        function handlePaystackClose() {
                            //Verify payment status 
                            console.log("Handling Paystack close event");
                            alert("Paystack window closed");
                        }

                        function paystacksuccess(e) {
                            const transaction = e;
                            const form = document.getElementById('submitPolicy');
                            const formData = new FormData(form);
                            formData.append('paystackreference', transaction);

                            console.log("Form data to be Sent: ", ...formData.entries());
                        }
                    </script>
                </x-layouts.app>
