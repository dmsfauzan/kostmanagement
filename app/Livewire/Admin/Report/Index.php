<?php

namespace App\Livewire\Admin\Report;

use App\Models\Property;
use App\Services\ReportService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(except: 'occupancy')]
    public string $type = 'occupancy';

    #[Url(except: '')]
    public string $property = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function render()
    {
        Gate::authorize('report.view');

        $reports = app(ReportService::class);

        $report = $reports->report($this->type, [
            'property' => $this->property ?: null,
            'from' => $this->from ?: null,
            'to' => $this->to ?: null,
        ]);

        return view('livewire.admin.report.index', [
            'report' => $report,
            'types' => $reports->types(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Laporan']);
    }
}
