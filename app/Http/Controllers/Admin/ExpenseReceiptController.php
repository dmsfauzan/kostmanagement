<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseReceiptController extends Controller
{
    public function download(Expense $expense): StreamedResponse
    {
        abort_unless($expense->receipt_path, 404);

        $disk = Storage::disk(config('kost.disk.private'));
        abort_unless($disk->exists($expense->receipt_path), 404);

        return $disk->download($expense->receipt_path, basename($expense->receipt_path));
    }
}
