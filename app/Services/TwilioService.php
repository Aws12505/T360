<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        // Get Twilio credentials
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        
        // Create Twilio client
        $this->twilio = new Client($sid, $token);
        
        // Create a custom CurlClient with SSL verification turned off for local development
        if (app()->environment('local')) {
            // Use the options array to disable SSL verification
            $httpClient = new CurlClient([
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            ]);
            
            // Set the custom HTTP client
            $this->twilio->setHttpClient($httpClient);
        }
    }

    public function sendSms($to, $message)
    {
        $response = $this->twilio->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
        
        return $response;
    }
}
