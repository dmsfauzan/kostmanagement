<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $tenant = request()->user()
            ->tenant()
            ->with(['property'])
            ->first();

        $lease = $tenant?->leases()
            ->with(['room.property', 'deposit'])
            ->whereIn('status', ['active', 'expiring'])
            ->latest()
            ->first();

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'lease' => $lease,
        ]);
    }
}
