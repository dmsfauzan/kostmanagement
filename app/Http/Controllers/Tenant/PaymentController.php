<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        abort_unless($tenant, 404);

        $payments = Payment::query()
            ->where('tenant_id', $tenant->id)
            ->with(['invoice', 'method'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tenant.payments.index', [
            'payments' => $payments,
        ]);
    }
}
