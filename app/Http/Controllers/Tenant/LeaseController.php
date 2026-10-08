<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LeaseController extends Controller
{
    public function __invoke(): View
    {
        $tenant = request()->user()
            ->tenant()
            ->with([
                'property',
                'leases.room.property',
                'leases.deposit',
                'leases.statusHistories',
            ])
            ->first();

        abort_unless($tenant, 404);

        return view('tenant.lease', [
            'tenant' => $tenant,
            'activeLease' => $tenant->leases
                ->whereIn('status', ['active', 'expiring'])
                ->sortByDesc('start_date')
                ->first(),
            'leases' => $tenant->leases->sortByDesc('start_date'),
        ]);
    }
}
