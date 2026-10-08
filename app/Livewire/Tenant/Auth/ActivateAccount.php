<?php

namespace App\Livewire\Tenant\Auth;

use App\Models\User;
use App\Services\TenantService;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;

class ActivateAccount extends Component
{
    public User $user;

    public string $name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->tenant?->full_name ?? $user->name;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function activate(): void
    {
        $data = $this->validate();

        $tenant = $this->user->tenant()->firstOrFail();
        app(TenantService::class)->activate($tenant, $data['password'], $data['name']);

        session()->flash('status', 'Akun berhasil diaktifkan. Silakan masuk dengan kata sandi baru Anda.');

        $this->redirectRoute('login', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tenant.auth.activate-account')
            ->layout('layouts.guest');
    }
}
