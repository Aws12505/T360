<?php

namespace App\Http\Controllers\Web\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Impersonation\ImpersonationService;
use Illuminate\Support\Facades\Auth;

/**
 * Class ImpersonationController
 *
 * This controller handles starting and stopping user impersonation.
 *
 * Command:
 *   php artisan make:controller Web/ImpersonationController
 */
class ImpersonationController extends Controller
{
    protected ImpersonationService $impersonationService;

    /**
     * Constructor.
     *
     * @param ImpersonationService $impersonationService Service for user impersonation logic.
     */
    public function __construct(ImpersonationService $impersonationService)
    {
        $this->impersonationService = $impersonationService;
    }

    /**
     * Start impersonating a user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate($id)
    {
        return $this->impersonationService->startImpersonation($id);
    }

    /**
     * Stop impersonation and revert to the original user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        return $this->impersonationService->stopImpersonation();
    }
}
