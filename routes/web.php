<?php

use App\Http\Controllers\AgentsdetailsModelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LgaController;
use App\Http\Controllers\NiipvehicleuseController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\StatesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiclecolorController;
use App\Http\Controllers\VehicleMakeController;
use App\Http\Controllers\VehicleModelController;
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
  
Route::middleware('auth')->group(function () {
    Route::get('list_policy',[PolicyController::class, 'index'])->name('list_policy'); 
    Route::get('buypolicy',[PolicyController::class, 'buypolicy'])->name('buy_policy');
    Route::get('new_policy',[PolicyController::class, 'newpolicy'])->name('new_policy');
    Route::get('view_policy',[PolicyController::class, 'viewpolicy'])->name('view_policy');
    Route::post('submit_mpolicy',[PolicyController::class, 'submitmpolicy'])->name('submit_mpolicy');
    Route::post('confirm_mpolicy',[PolicyController::class, 'confirmmpolicy'])->name('confirm_mpolicy');
    Route::post('pay_policy',[PolicyController::class, 'paypolicy'])->name('pay_policy');
    Route::get('test_async',[PolicyController::class, 'testasync'])->name('test_async');
    Route::post('/filter_report',[PolicyController::class, 'filterreport'])->name('filterreport');
    
    Route::get('retry_niip',[PolicyController::class, 'retryniip'])->name('retry_niip');
    Route::get('init_paystack',[PolicyController::class, 'init_paystack'])->name('init_paystack');


});

Route::get('error', function () {
  return view('user_errors');
})->name('uerror');

Route::middleware('auth')->group(function () {
    Route::get('list_users',[UserController::class, 'index'])->name('list_users'); 
    Route::post('update_user',[UserController::class, 'updateuser'])->name('update_user');    
});

Route::middleware('auth')->group(function () {
    Route::get('list_agents',[AgentsdetailsModelController::class, 'index'])->name('list_agents'); 
    Route::get('agent_profile',[AgentsdetailsModelController::class, 'agentprofile'])->name('agentprofile');  
    Route::post('agent_update',[AgentsdetailsModelController::class, 'agentupdate'])->name('agentupdate'); 
    Route::get('generate-api-token',[UserController::class, 'generateeapitoken'])->name('generateeapitoken');

});
Route::middleware('auth')->group(function () {
    Route::get('niip_code_mgmt', [DashboardController::class, 'niipmgtdashboard'])->name('niip_code_mgmt'); 


});


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
    

});

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');
});



require __DIR__ . '/auth.php';
