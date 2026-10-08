<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, ReportService $reports, ExportService $export): Response
    {
        $type = (string) $request->query('type', 'occupancy');
        $format = (string) $request->query('format', 'csv');

        $filters = [
            'property' => $request->integer('property') ?: null,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];

        $report = $reports->report($type, $filters);
        $filename = 'laporan-'.$type.'-'.now()->format('Ymd');

        return match ($format) {
            'xlsx', 'excel' => $export->excel($filename, $report['headings'], $report['rows']),
            'pdf' => $export->pdf($filename, $report['title'], $report['headings'], $report['rows']),
            default => $export->csv($filename, $report['headings'], $report['rows']),
        };
    }
}
