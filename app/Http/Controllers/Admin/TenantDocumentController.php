<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantDocumentController extends Controller
{
    public function download(TenantDocument $document): StreamedResponse
    {
        $disk = Storage::disk(config('kost.disk.private'));

        abort_unless($disk->exists($document->file_path), 404);

        return $disk->download($document->file_path, basename($document->file_path));
    }
}
