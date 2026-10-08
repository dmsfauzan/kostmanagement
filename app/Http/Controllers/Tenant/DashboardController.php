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

        $currentInvoice = $tenant
            ? $tenant->invoices()->unpaid()->orderBy('due_date')->with('items')->first()
            : null;

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'lease' => $lease,
            'currentInvoice' => $currentInvoice,
        ]);
    }
}
