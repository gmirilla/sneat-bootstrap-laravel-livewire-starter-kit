                @php
                use App\Models\agentsdetailsModel;
                    Auth::check();
                    $usercheck = Auth::user();
                    $agent=agentsdetailsModel::where('uid',$usercheck->id)->first();
                    $creditleft=$agent->noallocated - $agent->noused;

                    if ($usercheck->role=='subagent') {
                        # code...
                        $creditleft=$agent->subcreditassigned - $agent->subcreditused;
                    }
            
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
<script src="https://js.paystack.co/v2/inline.js"> </script>
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
     <div class="card col-9 mb-3">
        <div class="card-header"><h4>CONFIRM POLICY DETAILS BELOW</h4></div>
        </div>
<form id="paymentForm" action="{{route('pay_policy_old')}}" method="post">
    @csrf
    <div class="card col-9 mb-3">
        <div class="card-header"><h4>PRODUCT DETAILS</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                <div class="col-auto">
                    <label for="" class="form-label">PRODUCT</label>
                    <input class="form-control form-control-lg" type="text" disabled value="MOTOR THIRD PARTY">
                </div>
                <div class="col-auto">
                    <label for="" class="form-label">PRODUCT TYPE</label>
                    <input class="form-control form-control-lg" type="text" disabled value={{$policy->producttype}}>
                    <input class="form-control form-control-lg" type="text" name='policyid' hidden value="{{$policy->id}}">
                </div>
                <div class="col-auto">
                    <label for="datefrom" class="form-label">DATE FROM</label>
                    <input class="form-control form-control-lg" type="text" disabled value="{{$policy->start_date}}">
                </div>
                <div class="col-auto">
                    <label for="" class="form-label">DATE TO</label>
                    <input class="form-control form-control-lg" type="text" disabled value="{{$policy->end_date}}">
                    
                </div>
            </div>
        </div></div>
    <div class="card col-9 mb-3">
        <div class="card-header"><h4>PERSONAL DETAILS</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                 <div class="col-auto">
                    <label class='form-label' for="fname">Name on Certificate</label>
                    <input class='form-control form-control-lg' type="text" disabled name="name"  id="name" value="{{$policy->insured_name}}">
                </div>
            </div>

        </div>
    </div>
    
    <div class="card col-9 mb-3">
        <div class="card-header"><h4>VEHICLE DETAILS</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                <div class="col-auto">
                    <label class='form-label' for="regno">Registeration No.</label>
                    <input class='form-control form-control-lg' type="text" name="regno"  disabled id="regno" value="{{$policyrisk->regno}}">

                </div>
                <div class="col-auto">
                    <label class='form-label' for="chassisno">Chassis No.</label>
                    <input class='form-control form-control-lg' type="text" name="chassisno" disabled id="chassisno" value="{{$policyrisk->chassisno}}">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="engineno">Engine No.</label>
                    <input class='form-control form-control-lg' type="text" name="engineno" disabled id="engineno" value="{{$policyrisk->engineno}}">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="vehiclemake">Vehicle Make</label>
                    <input class='form-control form-control-lg' type="text" name="vehiclemake" disabled id="vehiclemake" value="{{$policyrisk->vehiclemake}}">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="vehiclemake">Vehicle Model</label>
                    <input class='form-control form-control-lg' type="text" name="vehiclemodel" disabled id="vehiclemodel" value="{{$policyrisk->vehiclemodel}}">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="yearofmake">Year of Make</label>
                    <input type="number" class="form-control form-control-lg" id="yearofmake" disabled name="yearofmake" value="{{$policyrisk->yearofmake}}">

                </div>
                <div class="col-auto">
                    <label class='form-label' for="vehiclecolor">Vehicle Color</label>
                    <input type="text" class="form-control form-control-lg" disabled value="{{$policyrisk->vehiclecolor}}">
                </div>
            </div>

        </div>
    </div>
     <div class="card col-9 mb-3">
        <div class="card-header"><h4>SELECT PAYMENT METHOD</h4></div>
        <div class="card-body">
            <input type="text" name='policyid' hidden value="{{$policy->id}}">
            <div class="row gy-2 gx-3 align-items-center mb-3">
        <div class="d-flex flex-row-reverse bd-highlight">
            <div class="p-2 bd-highlight" style="margin-right: 5px">
                @if ($accesscode !=null)
                <button onclick="paywithpaystack(event)" class="btn btn-primary" type="button" name="paystack" data-acode="{{$accesscode}}" data-toggle="tooltip" data-placement="right" title="Pay Using Paystack">Paystack</button>
                @endif

            </div>
            <div class="p-2 bd-highlight" style="margin-right: 5px">
                <button class="btn btn-primary" type="submit" disabled name="moniepoint" data-toggle="tooltip" data-placement="right"title="Coming Soon">Moniepoint</button>
            </div>
            @if (in_array($user->role, ['agent', 'subagent', 'user']) && ($agent->allowcredit==true) && ($creditleft>=0))
            <div class="p-2 bd-highlight" style="margin-right: 5px">  
                <button class="btn btn-primary"
        type="button"
        onclick="showProcessingAndSubmit(this)"
        id="acreditbtn">
Agency Credit</button>
            </div>
             <div class="p-2 bd-highlight" style="margin-right: 5px"><h5>You Have {{$creditleft}} Upload Credit(s) left: </h5>  
               
            </div>
            @endif
            
        </div>
            </div>
        </div>

     </div>
     </form>
     </div>



     
<script>
function showProcessingAndSubmit(btn) {
    // Show overlay
    document.getElementById("processingOverlay").style.display = "flex";

    // Optional: change button text
    btn.innerText = "Processing...";
        let input = document.createElement("input");
    input.type = "hidden";
    input.name = "agencycredit";
    input.value = "1";

    btn.form.appendChild(input);
    btn.form.submit();


    // Submit the form
    btn.form.submit();
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
<style>
    #processingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
        font-size: 2rem;
        font-weight: bold;
    }
</style>

<div id="processingOverlay">
    Processing...
</div>

</x-layouts.app>


