<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ZohoWebhookService;

/**
 * Class ZohoWebhookController
 *
 * This controller handles incoming webhook requests from Zoho.
 * It delegates processing to the ZohoWebhookService.
 *
 * Command:
 *   php artisan make:controller Web/ZohoWebhookController
 */
class ZohoWebhookController extends Controller
{
    protected ZohoWebhookService $zohoWebhookService;

    /**
     * Constructor.
     *
     * @param ZohoWebhookService $zohoWebhookService Service to process the webhook.
     */
    public function __construct(ZohoWebhookService $zohoWebhookService)
    {
        $this->zohoWebhookService = $zohoWebhookService;
    }

    /**
     * Process the Zoho webhook request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleZohoWebhook(Request $request)
    {
        return $this->zohoWebhookService->processWebhook($request);
    }
}
