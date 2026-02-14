<form method="POST" action="{{route('pay_policy')}}">
@csrf
<input type="hidden" name="policy_id" value="{{$policyId}}">

<div class="d-flex gap-3">

@if($methods['paystack'])
<button type="button" 
        class="btn btn-primary"
        onclick="PaystackPayment.start({{$policyId}})">
    Pay with Paystack
</button>
@endif

@if($methods['credit'])
<button type="submit" name="agency_credit" value="1" class="btn btn-success">
    Use Agency Credit ({{$creditLeft}})
</button>
@endif

</div>

<input type="hidden" name="paystack_reference" id="paystack_reference">

</form>
