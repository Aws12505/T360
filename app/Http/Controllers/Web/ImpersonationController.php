<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
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
    protected UserService $userService;

    /**
     * Constructor.
     *
     * @param UserService $userService Service for user impersonation logic.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Start impersonating a user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate($id)
    {
        return $this->userService->startImpersonation($id);
    }

    /**
     * Stop impersonation and revert to the original user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        return $this->userService->stopImpersonation();
    }
}
