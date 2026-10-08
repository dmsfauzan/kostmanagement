<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        $tenant = $user->tenant()->with(['property'])->first();

        $lease = $tenant?->leases()
            ->with(['room.property', 'deposit'])
            ->whereIn('status', ['active', 'expiring'])
            ->latest()
            ->first();

        $currentInvoice = $tenant
            ? $tenant->invoices()->unpaid()->orderBy('due_date')->with('items')->first()
            : null;

        $latestAnnouncement = Announcement::query()
            ->visible()
            ->forUser($user->id)
            ->latest('publish_at')
            ->first();

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'lease' => $lease,
            'currentInvoice' => $currentInvoice,
            'latestAnnouncement' => $latestAnnouncement,
        ]);
    }
}
