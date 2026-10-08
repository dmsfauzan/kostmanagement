<?php

namespace App\Livewire\Admin\Tenant;

use App\Enums\TenantDocumentType;
use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\TenantService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Tenant $tenant;

    public string $documentType = 'ktp';

    public ?string $documentNumber = '';

    public $document;

    public function mount(Tenant $tenant): void
    {
        Gate::authorize('tenant.view');
        $this->tenant = $tenant;
    }

    public function uploadDocument(): void
    {
        Gate::authorize('tenant_document.create');

        $this->validate([
            'documentType' => ['required', 'string'],
            'documentNumber' => ['nullable', 'string', 'max:60'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $path = $this->document->store("tenant-documents/{$this->tenant->id}", config('kost.disk.private'));

        $document = $this->tenant->documents()->create([
            'type' => $this->documentType,
            'number' => $this->documentNumber ?: null,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        app(AuditService::class)->record('tenant_document.uploaded', $document, [], [
            'type' => $this->documentType,
        ], 'TenantDocument');

        $this->reset(['document', 'documentNumber']);
        session()->flash('status', 'Dokumen berhasil diunggah.');
    }

    public function deleteDocument(int $id): void
    {
        Gate::authorize('tenant_document.delete');

        $document = $this->tenant->documents()->findOrFail($id);

        Storage::disk(config('kost.disk.private'))->delete($document->file_path);

        app(AuditService::class)->record('tenant_document.deleted', $document, ['type' => $document->type->value], [], 'TenantDocument');

        $document->delete();
        session()->flash('status', 'Dokumen berhasil dihapus.');
    }

    public function resendInvite(): void
    {
        Gate::authorize('tenant.update');

        if (blank($this->tenant->email)) {
            session()->flash('error', 'Penghuni tidak memiliki email.');

            return;
        }

        app(TenantService::class)->invite($this->tenant);
        session()->flash('status', 'Undangan aktivasi telah dikirim ke '.$this->tenant->email.'.');
    }

    public function render()
    {
        $tenant = $this->tenant->load([
            'property',
            'user',
            'documents.uploader',
            'leases' => fn ($query) => $query->with('room')->latest(),
        ]);

        return view('livewire.admin.tenant.show', [
            'tenant' => $tenant,
            'documentTypes' => TenantDocumentType::cases(),
        ])->layout('components.layouts.admin', ['title' => $tenant->full_name]);
    }
}
