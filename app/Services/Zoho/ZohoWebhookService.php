<?php

namespace App\Services\Zoho;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * Class ZohoWebhookService
 *
 * Processes and validates incoming Zoho webhook requests, then creates or retrieves the corresponding tenant and user.
 *
 * Created manually: touch app/Services/ZohoWebhookService.php
 */
class ZohoWebhookService
{
    /**
     * Process the Zoho webhook request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processWebhook(Request $request)
    {
        $secret = env("zohoSecret");
        $zohoSignature = $request->header('X-Zoho-Webhook-Signature');
        if (!$zohoSignature) {
            return response()->json(['error' => 'Missing Zoho signature.'], 400);
        }
        $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);
        if (!hash_equals($computedSignature, $zohoSignature)) {
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }
        $payload = $request->input('data.subscription');
        if (!$payload) {
            return response()->json(['error' => 'Missing subscription data in payload.'], 400);
        }
        $customerData = $payload['customer'] ?? null;
        if (!$customerData) {
            return response()->json(['error' => 'Missing customer data in payload.'], 400);
        }
        $customFields = $customerData['custom_field_hash'] ?? null;
        if (!$customFields) {
            return response()->json(['error' => 'Missing custom fields in customer data.'], 400);
        }
        $requiredFields = ['cf_wanted_admin_email', 'cf_wanted_admin_password'];
        foreach ($requiredFields as $field) {
            if (empty($customFields[$field])) {
                return response()->json(['error' => "Missing required custom field: {$field}."], 400);
            }
        }
        $companyName = trim($customerData['company_name']);
        $words = preg_split('/\s+/', $companyName);
        $slug = count($words) === 1 ? strtolower($words[0]) : strtolower(implode('', array_map(fn($w) => substr($w, 0, 1), $words)));
        $originalSlug = $slug;
        $count = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . $count;
            $count++;
        }
        $uniqueCompanyName = $companyName;
        $companyCount = 1;
        while (Tenant::where('name', $uniqueCompanyName)->exists()) {
            $uniqueCompanyName = $companyName . ' ' . $companyCount;
            $companyCount++;
        }
        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            ['name' => $uniqueCompanyName]
        );
        $user = User::firstOrCreate(
            ['email' => $customFields['cf_wanted_admin_email']],
            [
                'name' => $customerData['first_name'] . ' ' . $customerData['last_name'],
                'password' => Hash::make($customFields['cf_wanted_admin_password']),
                'tenant_id' => $tenant->id,
            ]
        );
        return response()->json([
            'status' => 'success',
            'tenant' => [
                'id'   => $tenant->id,
                'slug' => $tenant->slug,
                'name' => $tenant->name,
            ],
            'user' => [
                'id'    => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name ?? '',
                'last_name'  => $user->last_name ?? '',
            ],
        ], 200);
    }
}
