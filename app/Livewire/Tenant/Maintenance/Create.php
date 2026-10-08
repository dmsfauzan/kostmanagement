<?php

namespace App\Livewire\Tenant\Maintenance;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Services\MaintenanceService;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $category = 'other';

    public string $priority = 'medium';

    public string $description = '';

    /** @var array<int, mixed> */
    public array $photos = [];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::enum(MaintenanceCategory::class)],
            'priority' => ['required', Rule::enum(MaintenancePriority::class)],
            'description' => ['required', 'string', 'max:2000'],
            'photos' => ['array', 'max:5'],
            'photos.*' => ['image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $tenant = auth()->user()->tenant;

        abort_unless($tenant, 404);

        $photos = $this->photos;
        unset($data['photos']);

        $ticket = app(MaintenanceService::class)->create($tenant, $data, $photos);

        session()->flash('status', 'Keluhan '.$ticket->ticket_number.' berhasil dikirim. Kami akan menindaklanjuti.');

        $this->redirectRoute('tenant.maintenance', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tenant.maintenance.create', [
            'categories' => MaintenanceCategory::cases(),
            'priorities' => MaintenancePriority::cases(),
        ])->layout('components.layouts.tenant', ['title' => 'Buat Keluhan']);
    }
}
