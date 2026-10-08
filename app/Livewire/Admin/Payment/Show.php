<?php

namespace App\Livewire\Admin\Payment;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Payment $payment;

    public string $rejection_reason = '';

    public function mount(Payment $payment): void
    {
        Gate::authorize('payment.view');
        $this->payment = $payment;
    }

    public function verify(): void
    {
        Gate::authorize('payment.verify');

        try {
            app(PaymentService::class)->verify($this->payment->refresh());
            session()->flash('status', 'Pembayaran terverifikasi; tagihan diperbarui.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function reject(): void
    {
        Gate::authorize('payment.reject');

        $this->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            app(PaymentService::class)->reject($this->payment->refresh(), $this->rejection_reason);
            session()->flash('status', 'Pembayaran ditolak.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function refund(): void
    {
        Gate::authorize('payment.verify');

        try {
            app(PaymentService::class)->refund($this->payment->refresh());
            session()->flash('status', 'Pembayaran dikembalikan; tagihan diperbarui.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function cancel(): void
    {
        Gate::authorize('payment.verify');

        try {
            app(PaymentService::class)->cancel($this->payment->refresh());
            session()->flash('status', 'Pembayaran dibatalkan.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.payment.show', [
            'payment' => $this->payment->load(['tenant', 'invoice', 'method', 'verifier', 'rejecter']),
        ])->layout('components.layouts.admin', ['title' => 'Pembayaran']);
    }
}
