<?php

use App\Http\Controllers\Api\ThirdPartyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/test-access', function () {
    return response()->json(['message' => 'Access granted to protected resource']);
});
Route::middleware('auth:sanctum')->group(function ()  {
    Route::post('third-party',[ThirdPartyController::class, 'newpolicy'])->name('newpolicy');  
});
