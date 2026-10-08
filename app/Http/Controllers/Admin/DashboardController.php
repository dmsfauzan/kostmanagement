<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Property;
use App\Services\FinancialService;
use App\Services\OccupancyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $propertyId = $request->integer('property') ?: null;
        $from = now()->copy()->firstOfMonth()->startOfDay();
        $to = now()->copy()->endOfMonth()->endOfDay();

        $occupancy = app(OccupancyService::class)->summary($propertyId);
        $financial = app(FinancialService::class)->summary($propertyId, $from, $to);

        return view('admin.dashboard', [
            'occupancy' => $occupancy,
            'financial' => $financial,
            'pendingPayments' => Payment::query()->pending()->with('tenant')->latest()->take(5)->get(),
            'overdueInvoices' => Invoice::query()->overdue()->with('tenant')->latest()->take(5)->get(),
            'slaTickets' => MaintenanceTicket::query()->open()->whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->with('tenant')->latest()->take(5)->get(),
            'expiringLeases' => Lease::query()->open()->whereDate('end_date', '<=', now()->addDays(30)->toDateString())->with('tenant')->latest()->take(5)->get(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'selectedProperty' => $propertyId,
        ]);
    }
}
