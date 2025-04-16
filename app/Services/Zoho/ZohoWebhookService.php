<?php

namespace App\Services\Zoho;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        // Create tenant and user, and capture billing address from customer data.
        list($tenant, $user) = $this->createTenant($customerData, $customFields);

        // Extract billing address from customer data (if provided)
        $billingAddress = $customerData['billing_address'] ?? null;

        $subscription = $this->createSubscription($tenant->id, $payload, $billingAddress);

        return response()->json([
            'status'       => 'success',
            'tenant'       => [
                'id'   => $tenant->id,
                'slug' => $tenant->slug,
                'name' => $tenant->name,
            ],
            'subscription' => [
                'id'               => $subscription->id,
                'subscription_id'  => $subscription->subscription_id,
                'next_billing_at'  => $subscription->next_billing_at->toDateString(),
                'last_billing_at'  => $subscription->last_billing_at->toDateString(),
                'price'            => $subscription->price,
                'currency_code'    => $subscription->currency_code,
                'billing_address'  => $subscription->billing_address,
            ],
            'user'         => [
                'id'         => $user->id,
                'email'      => $user->email,
                'first_name' => $user->first_name ?? '',
                'last_name'  => $user->last_name ?? '',
            ],
        ], 200);
    }

    /**
     * Create (or fetch) a Tenant and its associated User based on incoming payload.
     *
     * @param array $customerData
     * @param array $customFields
     * @return array [$tenant, $user]
     */
    private function createTenant(array $customerData, array $customFields): array
    {
        $companyName = trim($customerData['company_name']);
        $words = preg_split('/\s+/', $companyName);
        $slug = count($words) === 1 
            ? strtolower($words[0]) 
            : strtolower(implode('', array_map(fn($w) => substr($w, 0, 1), $words)));
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
                'name'      => trim($customerData['first_name'] . ' ' . $customerData['last_name']),
                'password'  => Hash::make($customFields['cf_wanted_admin_password']),
                'tenant_id' => $tenant->id,
            ]
        );
        return [$tenant, $user];
    }

    /**
     * Create (or update) a Subscription record linked to $tenantId.
     *
     * @param int $tenantId
     * @param array $payload
     * @param array|null $billingAddress
     * @return \App\Models\Subscription
     */
    private function createSubscription(int $tenantId, array $payload, ?array $billingAddress = null)
    {
        // Pull out the card & pricing info
        $card = $payload['card'] ?? [];
        $plan = $payload['plan'] ?? [];

        return Subscription::updateOrCreate(
            ['subscription_id' => $payload['subscription_id']],
            [
                'tenant_id'        => $tenantId,
                'name'             => $payload['name'] ?? $plan['name'] ?? null,
                'description'      => $plan['description'] ?? null,
                'price'            => $plan['price'] ?? $payload['amount'] ?? null,
                'currency_code'    => $payload['currency_code'] ?? null,
                'next_billing_at'  => $payload['next_billing_at'],
                'last_billing_at'  => $payload['last_billing_at'],
                'expiry_year'      => $card['expiry_year'] ?? null,
                'expiry_month'     => $card['expiry_month'] ?? null,
                'last_four_digits' => $card['last_four_digits'] ?? null,
                'card_type'        => $card['card_type'] ?? null,
                'payment_gateway'  => $card['payment_gateway'] ?? null,
                'billing_address'  => $billingAddress,
            ]
        );
    }
}
