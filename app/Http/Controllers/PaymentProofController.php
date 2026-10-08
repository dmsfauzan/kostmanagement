<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    public function __invoke(Payment $payment): StreamedResponse
    {
        $user = request()->user();

        $owns = $payment->tenant_id === $user->tenant?->id;
        $canViewAny = $user->isStaff();

        abort_unless($owns || $canViewAny, 404);
        abort_unless($payment->proof_path, 404);

        $disk = Storage::disk(config('kost.disk.private'));
        abort_unless($disk->exists($payment->proof_path), 404);

        return $disk->download($payment->proof_path, basename($payment->proof_path));
    }
}
