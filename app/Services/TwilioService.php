<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Support\Facades\App;

class TwilioService
{
    protected Client $twilio;

    public function __construct()
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->twilio = new Client($sid, $token);

        // Disable SSL verify in local for easier testing
        if (App::environment('local')) {
            $httpClient = new CurlClient([
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ]);
            $this->twilio->setHttpClient($httpClient);
        }
    }

    /**
     * Send an SMS via Twilio.
     *
     * @param string $to    E.164-formatted number (e.g. '+18123633582')
     * @param string $message
     * @return \Twilio\Rest\Api\V2010\Account\MessageInstance
     */
    public function sendSms(string $to, string $message)
    {
        return $this->twilio->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }
}
