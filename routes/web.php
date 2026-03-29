<?php

use App\Http\Controllers\AgentsdetailsModelController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LgaController;
use App\Http\Controllers\NiipManualController;
use App\Http\Controllers\NiipvehicleuseController;
use App\Http\Controllers\NinController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\StatesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiclecolorController;
use App\Http\Controllers\VehicleMakeController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\PolicyPaymentController;
use App\Http\Controllers\EcmrController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
  return view('welcome');
})->name('home');


/**
 * Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])  ->name('dashboard');
 * 
 */


Route::middleware('auth')->group(function () {
  Route::get('/dashboard',[DashboardController::class, 'index'])->name('dashboard');
  });


  Route::get('/ninverification',[NinController::class, 'index'])->name('ninverification.start');
  
Route::middleware('auth')->group(function () {

    // Policy listing & views
    Route::get('list_policy', [PolicyController::class, 'index'])->name('list_policy');
    Route::get('buypolicy', [PolicyController::class, 'buypolicy'])->name('buy_policy');
    Route::get('new_policy', [PolicyController::class, 'newpolicy'])->name('new_policy');
    Route::get('view_policy', [PolicyController::class, 'viewpolicy'])->name('view_policy');
    Route::get('list_policy_subagents', [PolicyController::class, 'listpolicySubagents'])->name('list_policy_subagents');

    // Policy actions
    Route::post('policy/submit_mpolicy', [PolicyController::class, 'submitmpolicy'])->name('submit_mpolicy');
    Route::post('policy/confirm_mpolicy', [PolicyController::class, 'confirmmpolicy'])->name('confirm_mpolicy');
    Route::post('policy/pay_policy', [PolicyController::class, 'paypolicy'])->name('pay_policy_old');
    Route::post('policy/renew_policy', [PolicyController::class, 'renewpolicy'])->name('renew_policy');

    // Reports & renewals
    Route::post('filter_report', [PolicyController::class, 'filterreport'])->name('filterreport');
     Route::post('subagent/filter_report', [PolicyController::class, 'subagentfilterreport'])->name('subagent.filterreport');
    Route::get('upcoming_renewals', [PolicyController::class, 'renewalslist'])->name('renewalslist');

    // Misc
    Route::get('test_async', [PolicyController::class, 'testasync'])->name('test_async');
    Route::get('retry_niip', [PolicyController::class, 'retryniip'])->name('retry_niip');

    // Paystack
    Route::get('init_paystack/{policy}', [PolicyController::class, 'init_paystack'])->name('init_paystack');

    // Payment confirmation (fixed: removed leading slash)
    Route::get('policy/{id}/confirm', [PolicyPaymentController::class, 'confirm'])->name('policy.confirm');

    // Payment processing
    Route::post('policy/pay', [PolicyPaymentController::class, 'pay'])->name('pay_policy');

});

Route::get('error', function () {
  return view('user_errors');
})->name('uerror');

Route::middleware('auth')->group(function () {
    Route::get('list_users',[UserController::class, 'index'])->name('list_users'); 
    Route::post('update_user',[UserController::class, 'updateuser'])->name('update_user');    
});


Route::middleware('auth')->group(function () {
    Route::get('ecmr_index',[ECMRController::class, 'index'])->name('ecmrs.index'); 

});

Route::middleware('auth')->group(function () {
    Route::get('list_agents',[AgentsdetailsModelController::class, 'index'])->name('list_agents'); 
    Route::get('agent_profile',[AgentsdetailsModelController::class, 'agentprofile'])->name('agentprofile');  
    Route::post('agent_update',[AgentsdetailsModelController::class, 'agentupdate'])->name('agentupdate'); 
    Route::get('generate-api-token',[UserController::class, 'generateeapitoken'])->name('generateeapitoken');
    Route::get('sub_agents',[AgentsdetailsModelController::class, 'subagentsList'])->name('list_sub_agents');
    Route::get('sub_agent/profile/{sid}',[AgentsdetailsModelController::class, 'subagentsprofile'])->name('subagent.profile');
    Route::post('sub_agents/register_new/{agent}',[AgentsdetailsModelController::class, 'registerSubAgent'])->name('register_sub_agent');
    Route::post('sub_agents/update_credit/add',[AgentsdetailsModelController::class, 'subAgentCreditAdd'])->name('subagent.credit.add');
    Route::post('sub_agents/update_credit/remove',[AgentsdetailsModelController::class, 'subAgentCreditRemove'])->name('subagent.credit.remove');
    Route::post('/subagent/{id}/reset-password', [UserController::class, 'resetPassword'])->name('subagent.resetpassword');


});
Route::middleware('auth')->group(function () {
    Route::get('niip_code_mgmt', [DashboardController::class, 'niipmgtdashboard'])->name('niip_code_mgmt'); 
});

#CLAIM ROUTES STILL TESTING

    Route::post('claim_check', [ClaimController::class, 'claimcheck'])->name('claim_check'); 





Route::middleware('auth')->group(function () {
    Route::get('list_state',[StatesController::class, 'index'])->name('list_states'); 
    Route::post('import_states',[StatesController::class, 'importstates'])->name('importstates');
    Route::get('/update-states', [StatesController::class, 'updatestates'])->name('updatestates');   

});

Route::middleware('auth')->group(function () {
    Route::get('list_color',[VehiclecolorController::class, 'index'])->name('list_colors'); 
    Route::post('import_color',[VehiclecolorController::class, 'importvcolor'])->name('importcolor'); 
    Route::get('/get-colors', [VehiclecolorController::class, 'getColors']);  
    Route::get('/update-colors', [VehiclecolorController::class, 'updateColors'])->name('updatecolors'); 

});

Route::middleware('auth')->group(function () {
    Route::get('list_vuse',[NiipvehicleuseController::class, 'index'])->name('list_vuse'); 
    Route::post('import_vuse',[NiipvehicleuseController::class, 'importvuse'])->name('importvuse');   

});

Route::middleware('auth')->group(function () {
    Route::get('list_lga',[LgaController::class, 'index'])->name('list_lga'); 
    Route::post('import_lga',[LgaController::class, 'importlga'])->name('importlga');  
    Route::get('/get-lga/{state}', [LgaController::class, 'getlgas']);
    Route::get('/update-lgas', [LgaController::class, 'updatelgas'])->name('updatelgas');    

});
Route::middleware('auth')->group(function () {
    Route::get('list_vmake',[VehicleMakeController::class, 'index'])->name('list_vmake'); 
    Route::post('import_vmakes',[VehicleMakeController::class, 'importvmake'])->name('importvmake');  
    Route::get('/get-vehicle-models/{make}', [VehicleMakeController::class, 'getModels']);  
    Route::get('/update-vmake', [VehicleMakeController::class, 'updatevmake'])->name('updatevmake');   

});

Route::middleware('auth')->group(function () {
    Route::get('list_vmodel',[VehicleModelController::class, 'index'])->name('list_vmodel'); 
    Route::post('import_vmodel',[VehicleModelController::class, 'importvmodel'])->name('importvmodel'); 
    Route::post('/niip-manual-import/import', [NiipManualController::class, 'uploadExcel'])->name('niipmanual.import');
    Route::get('/niip-manual', [NiipManualController::class, 'show'])->name('niipmanual.show');
    

});

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');
});



require __DIR__ . '/auth.php';
