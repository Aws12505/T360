<?php 

namespace App\Services\Impersonation;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
class ImpersonationService{
    
    /**
     * Start impersonating a user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startImpersonation($id)
    {
        if (is_null(Auth::user()->tenant_id)) {
            session(['original_user' => Auth::id(), 'impersonate' => $id]);
            $impersonatedUser = User::findOrFail($id);
            Auth::login($impersonatedUser);
            return redirect()->route('dashboard', $impersonatedUser->tenant->slug);
        }
        abort(403, 'Unauthorized.');
    }

    /**
     * Stop impersonating and revert to the original user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        session()->forget('impersonate');
        $originalUser = User::withoutGlobalScopes()->find(session('original_user'));
        if ($originalUser) {
            Auth::login($originalUser);
            session()->forget('original_user');
            return redirect()->route('admin.users.roles.index');
        }
        abort(403, 'Unable to revert impersonation.');
    }
}