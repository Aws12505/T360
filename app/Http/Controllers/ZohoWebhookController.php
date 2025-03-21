<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class zohoWebhookController extends Controller
{
    public function handleZohoWebhook(Request $request)
    {
        // Define your secret key
        $secret = env("zohoSecret");

        // Retrieve the Zoho signature from the headers
        $zohoSignature = $request->header('X-Zoho-Webhook-Signature');
        if (!$zohoSignature) {
            return response()->json(['error' => 'Missing Zoho signature.'], 400);
        }

        // Compute the HMAC hash of the request body using the secret
        $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        // Verify the signature
        if (!hash_equals($computedSignature, $zohoSignature)) {
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        // Extract necessary data from the payload
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

        // Validate required custom fields
        $requiredFields = ['cf_wanted_admin_email', 'cf_wanted_admin_password'];
        foreach ($requiredFields as $field) {
            if (empty($customFields[$field])) {
                return response()->json(['error' => "Missing required custom field: {$field}."], 400);
            }
        }
        $companyName = trim($customerData['company_name']);
$words = preg_split('/\s+/', $companyName);
if (count($words) === 1) {
    // If it's a single word, use the entire word as the slug
    $slug = strtolower($words[0]);
} else {
    // If more than one word, use the first letter of each word
    $slug = strtolower(implode('', array_map(function ($word) {
        return substr($word, 0, 1);
    }, $words)));
}
    
        // Ensure the slug is unique
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
        // Create or retrieve the tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            ['name' => $uniqueCompanyName]
        );

        // Create or retrieve the user associated with the tenant
        $user = User::firstOrCreate(
            ['email' => $customFields['cf_wanted_admin_email']],
            [
                'name' => $customerData['first_name'] . ' ' . $customerData['last_name'],
                'password' => Hash::make($customFields['cf_wanted_admin_password']),
                'tenant_id' => $tenant->id,
            ]
        );

        // Return success response with tenant and user details
        return response()->json([
            'status' => 'success',
            'tenant' => [
                'id' => $tenant->id,
                'slug' => $tenant->slug,
                'name' => $tenant->name,
            ],
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
        ], 200);
    }
}
