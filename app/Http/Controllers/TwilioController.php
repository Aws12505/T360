<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;

class TwilioController extends Controller
{
    protected TwilioService $twilioService;

    /**
     * Constructor.
     *
     * @param TwilioService $twilioService Service to process the webhook.
     */
    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Send SMS message
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSms(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $response = $this->twilioService->sendSms(
                $validated['to'],
                $validated['message']
            );
            
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}
