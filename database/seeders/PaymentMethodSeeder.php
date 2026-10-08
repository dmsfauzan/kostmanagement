<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Reference data — seeded in every environment.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Transfer BCA',
                'code' => 'transfer-bca',
                'type' => PaymentMethodType::Transfer,
                'account_name' => 'Budi Pemilik',
                'account_number' => '1234567890',
                'instructions' => 'Transfer ke rekening BCA lalu unggah bukti transfer.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Tunai',
                'code' => 'tunai',
                'type' => PaymentMethodType::Cash,
                'account_name' => null,
                'account_number' => null,
                'instructions' => 'Pembayaran tunai dicatat langsung oleh pengelola.',
                'sort_order' => 2,
            ],
            [
                'name' => 'E-Wallet',
                'code' => 'ewallet',
                'type' => PaymentMethodType::EWallet,
                'account_name' => 'Budi Pemilik',
                'account_number' => '081234567890',
                'instructions' => 'Kirim ke nomor e-wallet lalu unggah bukti.',
                'sort_order' => 3,
            ],
            [
                'name' => 'QRIS',
                'code' => 'qris',
                'type' => PaymentMethodType::Qris,
                'account_name' => null,
                'account_number' => null,
                'instructions' => 'Scan kode QRIS yang diberikan pengelola.',
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->firstOrCreate(
                ['code' => $method['code']],
                $method + ['is_active' => true],
            );
        }
    }
}
