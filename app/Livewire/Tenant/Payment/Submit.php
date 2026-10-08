<?php

namespace App\Livewire\Tenant\Payment;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Submit extends Component
{
    use WithFileUploads;

    #[Url(except: null)]
    public ?int $invoice = null;

    public ?int $invoice_id = null;

    public ?int $amount = null;

    public string $paid_at = '';

    public ?int $payment_method_id = null;

    public ?string $reference = '';

    public ?string $notes = '';

    public $proof;

    public function mount(): void
    {
        $this->paid_at = now()->toDateString();

        $tenant = auth()->user()->tenant;
        $preselected = $this->invoice
            ? $tenant?->invoices()->whereKey($this->invoice)->first()
            : null;
        $first = $preselected
            ?? $tenant?->invoices()->unpaid()->orderBy('due_date')->first();

        if ($first) {
            $this->invoice_id = $first->id;
            $this->amount = $first->amount_due;
        }
    }

    public function updatedInvoiceId(?int $invoiceId): void
    {
        if (! $invoiceId) {
            return;
        }

        $tenant = auth()->user()->tenant;
        $invoice = $tenant?->invoices()->whereKey($invoiceId)->first();

        if ($invoice) {
            $this->amount = $invoice->amount_due;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'reference' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:255'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ];

        if ($this->payment_method_id && PaymentMethod::query()->find($this->payment_method_id)?->requiresProof()) {
            $rules['proof'] = ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'];
        }

        return $rules;
    }

    public function save(): void
    {
        $data = $this->validate();

        $tenant = auth()->user()->tenant;

        abort_unless($tenant, 404);

        $invoice = Invoice::query()
            ->whereKey($data['invoice_id'])
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        try {
            app(PaymentService::class)->submit($invoice, $data, $this->proof);
        } catch (\InvalidArgumentException $thrown) {
            $this->addError('amount', $thrown->getMessage());

            return;
        }

        session()->flash('status', 'Bukti pembayaran telah dikirim dan menunggu verifikasi.');

        $this->redirectRoute('tenant.payments', navigate: true);
    }

    public function render(): View
    {
        $tenant = auth()->user()->tenant;

        abort_unless($tenant, 404);

        $invoices = $tenant->invoices()
            ->unpaid()
            ->with(['lease.room.property'])
            ->orderBy('due_date')
            ->get();

        return view('livewire.tenant.payment.submit', [
            'invoices' => $invoices,
            'methods' => PaymentMethod::query()->active()->orderBy('sort_order')->get(),
        ])->layout('components.layouts.tenant', ['title' => 'Bayar Tagihan']);
    }
}
