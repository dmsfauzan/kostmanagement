<?php

namespace App\Services;

use App\Exports\ArrayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * @param  list<string>  $headings
     * @param  list<array<int, mixed>>  $rows
     */
    public function csv(string $filename, array $headings, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $headings
     * @param  list<array<int, mixed>>  $rows
     */
    public function excel(string $filename, array $headings, array $rows): Response
    {
        return Excel::download(new ArrayExport($headings, $rows), $filename.'.xlsx');
    }

    /**
     * @param  list<string>  $headings
     * @param  list<array<int, mixed>>  $rows
     */
    public function pdf(string $filename, string $title, array $headings, array $rows): Response
    {
        return Pdf::loadView('exports.pdf', [
            'title' => $title,
            'headings' => $headings,
            'rows' => $rows,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->download($filename.'.pdf');
    }
}
