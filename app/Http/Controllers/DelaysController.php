<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Delay;
use App\Models\Tenant;
use App\Models\DelayCode;

class DelaysController extends Controller
{
    public function index()
    {
        $delays = Delay::with(['tenant','delayCode'])->get();
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $delayCodes = DelayCode::get();
        return inertia('Delays/Index', [
            'delays' => $delays,
            'tenantSlug' => $tenantSlug,
            'isSuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'delay_codes' => $delayCodes,
        ]);
    }

    public function store(Request $request)
{
    $user = Auth::user();

    $rules = [
        'date' => 'required|date',
        'driver_name' => 'required|string',
        'delay_type' => 'required|in:origin,destination',
        'delay_category' => 'required|in:1_120,121_600,601_plus',
        'delay_code_id' => 'required|exists:delay_codes,id',
        'disputed' => 'required|boolean',
        'driver_controllable' => 'nullable|boolean',
    ];

    // If superadmin, they can pick tenant
    if (is_null($user->tenant_id)) {
        $rules['tenant_id'] = 'required|exists:tenants,id';
    }

    $data = $request->validate($rules);
    $data['tenant_id'] = $user->tenant_id ?? $request->input('tenant_id');

    $data['penalty'] = match ($data['delay_category']) {
        '1_120' => 1,
        '121_600' => 2,
        '601_plus' => 4,
    };

    Delay::create($data);

    return back();
}

public function updateAdmin(Request $request, Delay $delay)
{
    $user = Auth::user();

    $rules = [
        'date' => 'required|date',
        'driver_name' => 'required|string',
        'delay_type' => 'required|in:origin,destination',
        'delay_category' => 'required|in:1_120,121_600,601_plus',
        'delay_code_id' => 'required|exists:delay_codes,id',
        'disputed' => 'required|boolean',
        'driver_controllable' => 'nullable|boolean',
    ];

    if (is_null($user->tenant_id)) {
        $rules['tenant_id'] = 'required|exists:tenants,id';
    }

    $data = $request->validate($rules);
    $data['tenant_id'] = $user->tenant_id ?? $request->input('tenant_id');

    $data['penalty'] = match ($data['delay_category']) {
        '1_120' => 1,
        '121_600' => 2,
        '601_plus' => 4,
    };

    $delay->update($data);

    return back();
}

    

public function destroyAdmin(Delay $delay)
{
    $delay->delete();

    return back();
}
public function update(Request $request,$tenantSlug, Delay $delay)
{
    $user = Auth::user();

    $rules = [
        'date' => 'required|date',
        'driver_name' => 'required|string',
        'delay_type' => 'required|in:origin,destination',
        'delay_category' => 'required|in:1_120,121_600,601_plus',
        'delay_code_id' => 'required|exists:delay_codes,id',
        'disputed' => 'required|boolean',
        'driver_controllable' => 'nullable|boolean',
    ];

    if (is_null($user->tenant_id)) {
        $rules['tenant_id'] = 'required|exists:tenants,id';
    }

    $data = $request->validate($rules);
    $data['tenant_id'] = $user->tenant_id ?? $request->input('tenant_id');

    $data['penalty'] = match ($data['delay_category']) {
        '1_120' => 1,
        '121_600' => 2,
        '601_plus' => 4,
    };

    $delay->update($data);

    return back();
}

    

public function destroy($tenantSlug, Delay $delay)
{
    $delay->delete();

    return back();
}
public function storeCode(Request $request)
{
    $data = $request->validate([
        'code' => 'required|string|unique:delay_codes,code',
    ]);

    DelayCode::create($data);

    return back();
}

public function destroyCode(DelayCode $delay_code)
{
    $delay_code->delete();

    return back();
}
    }
