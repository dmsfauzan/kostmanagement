<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Send each authenticated user to the dashboard for their role.
     */
    public function __invoke(): RedirectResponse
    {
        $user = request()->user();

        if ($user->hasRole('tenant')) {
            return redirect()->route('tenant.dashboard');
        }

        if ($user->hasAnyRole(['owner', 'admin', 'finance', 'technician'])) {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/');
    }
}
