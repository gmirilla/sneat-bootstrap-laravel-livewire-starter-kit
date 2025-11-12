<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
@php
    $user = auth()->user();
@endphp
<div class="col-12 col-md col-xl col-sm py-md-3 pl-md-5t  fs-6, fs-md-5, fs-lg-5, fs-xl-1">
<form action="{{route('submit_mpolicy')}}" method="post">
    @csrf
    <div class="card mb-3">
        <div class="card-header"><h4>PRODUCT DETAILS</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                <div class="col-auto">
                    <label for="" class="form-label">PRODUCT</label>
                    <input class="form-control form-control-lg" type="text" disabled value="MOTOR THIRD PARTY">
                </div>
                <div class="col-auto">
                    <label for="" class="form-label">PRODUCT TYPE</label>
                    <input class="form-control form-control-lg" type="text" disabled value="{{$producttype}}">
                    <input class="form-control form-control-lg" type="text" name='producttype' hidden value="{{$producttype}}">
                    <input class="form-control form-control-lg" type="text" name='insurancetype' hidden value="{{$insurancetype }}">
                    <input class="form-control form-control-lg" type="text" name='vehicleuse' hidden value="{{$vehicleuse}}">
                    <input type="text" name="niipusecode" id="niipusecode" hidden value="{{$niipusecode}}">
                </div>
                <div class="col-auto">
                    <label for="" class="form-label">CONTRIBUTION ( &#8358;)</label>
                    <input class="form-control form-control-lg" type="text" disabled value="{{ number_format($contribution, 2) }}">
                    <input class="form-control form-control-lg" type="number" name='contribution' hidden value="{{$contribution}}">
                </div>
            </div>
        </div></div>
    <div class="card mb-3">
        <div class="card-header"><h4>PERSONAL DETAILS</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                 <div class="col-auto">
                    <label class='form-label' for="fname">First Name</label>
                    <input class='form-control form-control-lg' type="text" name="fname"  required id="fname" placeholder="Enter First Name">

                </div>
                <div class="col-auto">
                    <label class='form-label' for="lname">Last Name</label>
                    <input class='form-control form-control-lg' type="text" name="lname"  required id="lname" placeholder="Enter Last Name">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="gender">Gender</label>
                    <select class='form-select form-control-lg' name="gender" id="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class='form-label' for="dob">Date of Birth</label>
                    <input class='form-control form-control-lg' type="date" name="dob"  required id="dob" 
                    placeholder="Select your Date of Birth" min="1925-01-01" max="2008-01-01">
                </div>

                <div class="col-auto">
                    <label class='form-label' for="email">Email</label>
                    <input class='form-control form-control-lg' type="email" name="email" id="email" placeholder="Enter Valid email Address">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="phone">Tel. No.</label>
                    <input class='form-control form-control-lg' type="tel" name="phone"  required id="phone" placeholder="Phone no.">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="state">State of Residence</label>
                    <select class='form-select form-control-lg'  required name="state" id="state" onchange="getlga()">
                        <option value="">Select State</option>
                        @foreach($states as $state) 
                        <option value="{{ $state->stateid }}">{{ $state->statename }}</option>
                        @endforeach
                    </select>  
                </div>
                <div class="col-auto">
                    <label class='form-label' for="state">LGA</label>
                    <select class='form-select form-control-lg'  required name="lgas" id="lgas" >
                        <option value="">Select LGA</option>
                    </select>  
                </div>
                <div class="row gy-2 gx-3 align-items-center mb-3">
                <div>
                    <label class='form-label' for="address">Address</label>
                    <textarea class='form-control form-control-lg' name="address"  required id="address" placeholder="Enter Address" rows="3"></textarea>

                </div>

                </div>

            </div>

        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h4>VEHICLE DETAILS</h4></div>
        <div class="card-body">
            <div class="row gy-2 gx-3 align-items-center mb-3">
                <div class="col-auto">
                    <label class='form-label' for="regno">Registeration No.</label>
                    <input class='form-control form-control-lg' type="text" name="regno"  required id="regno" placeholder="Registeration Number">

                </div>
                <div class="col-auto">
                    <label class='form-label' for="chassisno">Chassis No.</label>
                    <input class='form-control form-control-lg' type="text" name="chassisno" required id="chassisno" placeholder="Chassis Number">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="engineno">Engine No.</label>
                    <input class='form-control form-control-lg' type="text" name="engineno" required id="engineno" placeholder="Engine Number">
                </div>
                <div class="col-auto">
                    <label class='form-label' for="vehiclemake">Vehicle Make</label>
                    <select name="vehiclemake" required class="form-select form-control-lg" id="vehiclemake" onchange="test()">
                         <option value="">Select Make</option>
                            @foreach($vmakes as $vmake)
                             <option value="{{ $vmake->niipvmid }}">{{ $vmake->vmake }}</option>
                            @endforeach
                    </select >

                </div>
                <div class="col-auto">
                    <label class='form-label' for="vehiclemake">Vehicle Model</label>
                      <select id="vehiclemodel" required class="form-select form-control-lg" name="vmodel">
    <option value="">Select Model</option>
</select>

</div>

                <div class="col-auto">
                    <label class='form-label' for="yearofmake">Year of Make</label>
                    <input type="number" class="form-control form-control-lg" id="yearofmake" 
                    placeholder="Enter Year of Make"
                    name="yearofmake" 
                    min="1970" max="{{date('Y')}}">

                </div>
                <div class="col-auto">
                    <label class='form-label' for="vehiclecolor">Vehicle Color</label>
                    
                    <select name="vehiclecolor" id="colors" class="form-select form-control-lg" >
                        <option value="">Select Color</option>
                        @foreach($colors as $color)
                        <option value="{{ $color->colorid }}">{{ $color->color }}</option>
                        @endforeach

                    </select>
                </div>
                <div class="col-auto">
                    <input type="text"  name="vehicletype" id="vehicletype"  hidden value="{{$usekey}}">
                </div>
            </div>

        </div>
    </div>
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
        @if ($user->role=='agent'|| $user->role=='user')            
        
        <div class="d-flex flex-row-reverse bd-highlight">
            <div class="p-2 bd-highlight" style="margin-right: 5px">
                <button class="btn btn-primary" type="submit">Submit Policy</button>
            </div>
     
        </div>
        @endif
     </div>
     </form>
</div>



<script>
function test(params) {

    let e = document.getElementById('vehiclemake');
    var value = e.value;
    var text = e.options[e.selectedIndex].text;
        $.ajax({
            url: '/get-vehicle-models/' + value,
            type: 'GET',
            success: function(models) {
                $('#vehiclemodel').html('');
                models.forEach(function(model) {
                    $('#vehiclemodel').append('<option value="' + model.vmodelid + '">' + model.vmodelname + '</option>');
                });
            }
        });

}

function getlga(params) {

    let e = document.getElementById('state');
  
    var value = e.value;
    var text = e.options[e.selectedIndex].text;
        $.ajax({
            url: '/get-lga/' + value,
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

