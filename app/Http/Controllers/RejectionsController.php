<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Rejection;
use App\Models\Tenant;
use App\Models\RejectionReasonCode;

class RejectionsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = is_null($user->tenant_id);
$rejections=Rejection::with(['tenant', 'reasonCode'])->get();
        return inertia('Rejections/Index', [
            'rejections' => $rejections,
            'tenantSlug' => $isSuperAdmin ? null : $user->tenant->slug,
            'isSuperAdmin' => $isSuperAdmin,
            'tenants' => $isSuperAdmin ? Tenant::all() : [],
            'rejection_reason_codes' => RejectionReasonCode::get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = is_null($user->tenant_id);

        $rules = [
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'rejection_type' => 'required|in:block,load',
            'rejection_category' => 'required|in:more_than_6,within_6,after_start',
            'reason_code_id' => 'required|exists:rejection_reason_codes,id',
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
        ];

        if ($isSuperAdmin) {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        $data = $request->validate($rules);
        $data['tenant_id'] = $isSuperAdmin ? $data['tenant_id'] : $user->tenant_id;

        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6' => 4,
            'after_start' => 8,
        };
        Rejection::create($data);

        return back();
    }

    public function updateAdmin(Request $request, Rejection $rejection)
    {
        $user = Auth::user();
        $isSuperAdmin = is_null($user->tenant_id);

        $rules = [
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'rejection_type' => 'required|in:block,load',
            'rejection_category' => 'required|in:more_than_6,within_6,after_start',
            'reason_code_id' => 'required|exists:rejection_reason_codes,id',
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
        ];

        if ($isSuperAdmin) {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        $data = $request->validate($rules);
        $data['tenant_id'] = $isSuperAdmin ? $data['tenant_id'] : $user->tenant_id;

        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6' => 4,
            'after_start' => 8,
        };

        $rejection->update($data);

        return back();
    }
    public function update(Request $request,$tenantSlug, Rejection $rejection)
    {
        $user = Auth::user();
        $isSuperAdmin = is_null($user->tenant_id);

        $rules = [
            'date' => 'required|date',
            'driver_name' => 'required|string',
            'rejection_type' => 'required|in:block,load',
            'rejection_category' => 'required|in:more_than_6,within_6,after_start',
            'reason_code_id' => 'required|exists:rejection_reason_codes,id',
            'disputed' => 'required|boolean',
            'driver_controllable' => 'nullable|boolean',
        ];

        if ($isSuperAdmin) {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        $data = $request->validate($rules);
        $data['tenant_id'] = $isSuperAdmin ? $data['tenant_id'] : $user->tenant_id;

        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6' => 4,
            'after_start' => 8,
        };

        $rejection->update($data);

        return back();
    }
    public function destroyAdmin(Rejection $rejection)
    {
        $rejection->delete();
        return back();
    }
    public function destroy($tenantSlug,Rejection $rejection)
    {
        $rejection->delete();
        return back();
    }
    public function storeCode(Request $request)
    {
        $data = $request->validate([
            'reason_code' => 'required|string|unique:rejection_reason_codes,reason_code',
        ]);

        RejectionReasonCode::create($data);
        return back();
    }

    public function destroyCode(RejectionReasonCode $rejection_reason_code)
    {
        $rejection_reason_code->delete();
        return back();
    }
}
