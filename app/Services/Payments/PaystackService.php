<?php
namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    public function verify(string $reference)
    {
        return Http::withToken(config('services.paystack.secret'))
            ->get("https://api.paystack.co/transaction/verify/{$reference}")
            ->object();
    }
}
