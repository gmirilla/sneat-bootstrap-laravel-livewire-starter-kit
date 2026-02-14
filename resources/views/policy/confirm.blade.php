<x-layouts.app>

<x-policy.summary :policy="$policy" :risk="$risk"/>

<x-policy.payment-methods 
    :methods="$paymentMethods"
    :creditLeft="$creditLeft"
    :policyId="$policy->id"
/>

</x-layouts.app>