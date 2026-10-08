<?php

namespace App\Livewire\Admin;

use App\Services\GlobalSearchService;
use Illuminate\View\View;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $term = '';

    public function clear(): void
    {
        $this->term = '';
    }

    public function render(): View
    {
        $groups = strlen(trim($this->term)) >= 2
            ? app(GlobalSearchService::class)->search($this->term)
            : [];

        return view('livewire.admin.global-search', [
            'groups' => $groups,
        ]);
    }
}
