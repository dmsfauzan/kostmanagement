<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
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

    public function cancel(Request $request, Payment $payment): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_unless($tenant && $payment->tenant_id === $tenant->id, 404);

        try {
            app(PaymentService::class)->cancel($payment);
            session()->flash('status', 'Pembayaran dibatalkan.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }

        return redirect()->route('tenant.payments');
    }
}
