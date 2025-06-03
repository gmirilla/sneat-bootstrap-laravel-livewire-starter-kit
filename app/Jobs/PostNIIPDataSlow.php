<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\policy;
use Illuminate\Support\Facades\Http;



class PostNIIPDataSlow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data; // Store the passed data
    }

    public function handle()
    {
        $policy = policy::where('policyno', $this->data['PolicyNumber'])->first();
        $niipdata=$this->data;
        $niipdatajSon=json_encode($niipdata);

            try{
                $niipresponse = Http::withBody($niipdatajSon)->timeout(180)->post(config('variables.NIIP_URL'));
              // Set timeout to 180 seconds
                //handle niip response
            
                $niipresponsedata = json_decode($niipresponse->body(), true);

            }
            catch (\Exception $e) {
                # code...
                $policy->niip_status='Error: '.$e->getMessage();
                $policy->save();

            }


                $policy->niip_status='Error: '.$e->getMessage();
                $policy->save();
        Log::info('Processing Data:', ['data' => $this->data]);
        Log::info('Error Data:', [$niipresponsedata['message']]);
    }

}