<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        abort_unless($tenant, 404);

        $invoices = Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->with(['lease.room.property'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tenant.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice): View
    {
        abort_unless($invoice->tenant_id === request()->user()->tenant?->id, 404);

        return view('tenant.invoices.show', [
            'invoice' => $invoice->load(['lease.room.property', 'items']),
        ]);
    }
}
