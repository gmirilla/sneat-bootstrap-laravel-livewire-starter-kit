<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
    
$policydata=[
      "fname"=>"Geraldine",
  "lname"=>"Pinholio2",
  "email"=> "gpinhole2@test.com",
  "gender"=> "MALE",
  "dob"=> "01-Jan-2019",
  "phone"=> "+234897898812",
  "state"=> "1",
  "lga"=> "15",
  "address"=>"TBA",
  "vehicleuse"=>"motorcycle",
  "vehiclemakeid"=> 1,
  "vehiclemodelid"=>45,
  "vehiclecolor"=>11,
  "regno"=>"qwertyyu",
  "engineno"=> "1233445grfmg4k",
  "chassisno"=>"koplawrs1123ed",
  "yearofmake"=> "2010"
];
$accesstoken="1|tuGVMwfdBjJeki99IoSvAImskSM3QslZhehPINAY94c1341d";
 $policydatajSon=json_encode($policydata);
     $response = Http::withHeader('Auth-Token',$accesstoken)->withBody($policydatajSon)
                ->post("localhost:8000/api/newpolicy");
                return "xx";
?>
<x-layouts.app>


Testing API
</x-layouts.app>