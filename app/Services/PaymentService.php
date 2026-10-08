<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Events\PaymentRejected;
use App\Events\PaymentSubmitted;
use App\Events\PaymentVerified;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Tenant submits a payment proof (stays pending until verified).
     *
     * @param  array{amount: int, paid_at?: string, payment_method_id?: int, reference?: string, notes?: string}  $data
     */
    public function submit(Invoice $invoice, array $data, ?UploadedFile $proof = null): Payment
    {
        if (! $invoice->isPayable()) {
            throw new InvalidArgumentException('Tagihan tidak dapat dibayar pada status saat ini.');
        }

        $amount = (int) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih dari nol.');
        }

        $method = isset($data['payment_method_id'])
            ? PaymentMethod::query()->findOrFail($data['payment_method_id'])
            : null;

        if ($method && $method->requiresProof() && ! $proof) {
            throw new InvalidArgumentException('Bukti pembayaran wajib diunggah untuk metode ini.');
        }

        $payment = Payment::query()->create([
            'property_id' => $invoice->property_id,
            'invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'amount' => $amount,
            'paid_at' => $data['paid_at'] ?? now()->toDateString(),
            'payment_method_id' => $method?->id,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => PaymentStatus::Pending,
        ]);

        if ($proof) {
            $path = $proof->store("payments/{$payment->id}", config('kost.disk.private'));
            $payment->update(['proof_path' => $path]);
        }

        $this->audit->record('payment.submitted', $payment, [], [
            'invoice' => $invoice->invoice_number,
            'amount' => $amount,
        ], 'Payment');

        event(new PaymentSubmitted($payment));

        return $payment->refresh();
    }

    /**
     * Admin records a payment directly (e.g. cash) and marks it verified.
     *
     * @param  array{amount: int, paid_at?: string, payment_method_id?: int, reference?: string, notes?: string}  $data
     */
    public function recordManual(Invoice $invoice, array $data): Payment
    {
        if ($invoice->isVoid()) {
            throw new InvalidArgumentException('Tagihan sudah dibatalkan.');
        }

        $amount = (int) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih dari nol.');
        }

        $payment = DB::transaction(function () use ($invoice, $data, $amount): Payment {
            $payment = Payment::query()->create([
                'property_id' => $invoice->property_id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'amount' => $amount,
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => PaymentStatus::Verified,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $this->recalculateInvoice($invoice);

            return $payment;
        });

        $this->audit->record('payment.recorded', $payment, [], [
            'invoice' => $invoice->invoice_number,
            'amount' => $amount,
        ], 'Payment');

        return $payment;
    }

    public function verify(Payment $payment): Payment
    {
        if (! $payment->isPending()) {
            throw new InvalidArgumentException('Hanya pembayaran menunggu yang dapat diverifikasi.');
        }

        DB::transaction(function () use ($payment): void {
            $payment->update([
                'status' => PaymentStatus::Verified,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $this->recalculateInvoice($payment->invoice);
        });

        $this->audit->record('payment.verified', $payment, ['status' => 'pending'], [
            'status' => 'verified',
            'amount' => $payment->amount,
        ], 'Payment');

        event(new PaymentVerified($payment));

        return $payment->refresh();
    }

    public function reject(Payment $payment, string $reason): Payment
    {
        if (! $payment->isPending()) {
            throw new InvalidArgumentException('Hanya pembayaran menunggu yang dapat ditolak.');
        }

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->audit->record('payment.rejected', $payment, ['status' => 'pending'], [
            'status' => 'rejected',
            'reason' => $reason,
        ], 'Payment');

        event(new PaymentRejected($payment));

        return $payment->refresh();
    }

    public function refund(Payment $payment, string $notes = ''): Payment
    {
        if (! $payment->isVerified()) {
            throw new InvalidArgumentException('Hanya pembayaran terverifikasi yang dapat dikembalikan.');
        }

        DB::transaction(function () use ($payment): void {
            $payment->update(['status' => PaymentStatus::Refunded]);
            $this->recalculateInvoice($payment->invoice);
        });

        $this->audit->record('payment.refunded', $payment, ['status' => 'verified'], [
            'status' => 'refunded',
            'notes' => $notes,
        ], 'Payment');

        return $payment->refresh();
    }

    public function cancel(Payment $payment): Payment
    {
        if (! $payment->isPending()) {
            throw new InvalidArgumentException('Hanya pembayaran menunggu yang dapat dibatalkan.');
        }

        $payment->update(['status' => PaymentStatus::Cancelled]);

        $this->audit->record('payment.cancelled', $payment, ['status' => 'pending'], ['status' => 'cancelled'], 'Payment');

        return $payment->refresh();
    }

    /**
     * Derive amount_paid/amount_due and status from verified payments.
     */
    public function recalculateInvoice(Invoice $invoice): Invoice
    {
        $amountPaid = (int) $invoice->payments()->where('status', PaymentStatus::Verified->value)->sum('amount');
        $amountDue = max(0, $invoice->total_amount - $amountPaid);

        $status = $invoice->status;
        $paidAt = $invoice->paid_at;

        if ($invoice->status === InvoiceStatus::Void) {
            // keep void; only refresh amounts
        } elseif ($amountDue <= 0 && $amountPaid > 0) {
            $status = InvoiceStatus::Paid;
            $paidAt = $invoice->paid_at ?? now();
        } elseif ($amountPaid > 0) {
            $status = InvoiceStatus::PartiallyPaid;
            $paidAt = null;
        } elseif ($invoice->status !== InvoiceStatus::Draft) {
            $status = $invoice->due_date->isPast() ? InvoiceStatus::Overdue : InvoiceStatus::Issued;
            $paidAt = null;
        }

        $invoice->update([
            'amount_paid' => $amountPaid,
            'amount_due' => $amountDue,
            'status' => $status,
            'paid_at' => $paidAt,
        ]);

        return $invoice->refresh();
    }
}
