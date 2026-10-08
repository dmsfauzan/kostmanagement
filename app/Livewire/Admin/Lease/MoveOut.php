<?php

namespace App\Livewire\Admin\Lease;

use App\Models\Lease;
use App\Services\DepositService;
use App\Services\LeaseService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MoveOut extends Component
{
    public Lease $lease;

    public string $termination_reason = '';

    public int $deduction = 0;

    public int $refund = 0;

    public ?string $notes = '';

    public function mount(Lease $lease): void
    {
        Gate::authorize('lease.terminate');
        $this->lease = $lease;
        $this->refund = $lease->deposit?->amount ?? $lease->deposit_amount;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'termination_reason' => ['required', 'string', 'min:5', 'max:255'],
            'deduction' => ['required', 'integer', 'min:0'],
            'refund' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function process(): void
    {
        $data = $this->validate();

        $deposit = $this->lease->deposit;

        if (! $deposit) {
            $this->addError('refund', 'Deposit belum dibuat untuk kontrak ini.');

            return;
        }

        if ($deposit->amount < $data['deduction'] + $data['refund']) {
            $this->addError('refund', 'Total pengembalian dan potongan melebihi nominal deposit.');

            return;
        }

        try {
            app(DepositService::class)->settle($this->lease, $data['deduction'], $data['refund'], $data['notes']);
            app(LeaseService::class)->terminate($this->lease->refresh(), $data['termination_reason']);

            session()->flash('status', 'Move-out berhasil diproses dan deposit telah disettle.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());

            return;
        }

        $this->redirectRoute('admin.leases.show', ['lease' => $this->lease->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.lease.move-out', [
            'lease' => $this->lease->load(['tenant', 'room', 'deposit']),
        ])->layout('components.layouts.admin', ['title' => 'Move-Out']);
    }
}
