<x-card title="Policy Details">
    <x-field label="Product" :value="$policy->producttype ?? 'n/a'"/>
    <x-field label="Insured Name" :value="$policy->insured_name ?? 'n/a'"/>
    <x-field label="Start Date" :value="$policy->start_date ?? 'n/a'"/>
    <x-field label="End Date" :value="$policy->end_date ?? 'n/a'"/>

    <h5 class="mt-4">Vehicle</h5>
    <x-field label="Reg No" :value="$risk->regno ?? 'n/a'"/>
    <x-field label="Chassis" :value="$risk->chassisno ?? 'n/a'"/>
    <x-field label="Engine" :value="$risk->engineno ?? 'n/a'"/>
</x-card>
