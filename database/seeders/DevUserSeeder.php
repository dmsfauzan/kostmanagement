<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevUserSeeder extends Seeder
{
    /**
     * Development accounts. Never used in production.
     *
     * Password is taken from KOST_DEV_PASSWORD (falls back to "password").
     *
     * @var array<int, array{name: string, email: string, role: string}>
     */
    public const USERS = [
        ['name' => 'Budi Pemilik', 'email' => 'owner@kostmanagement.test', 'role' => 'owner'],
        ['name' => 'Siti Admin', 'email' => 'admin@kostmanagement.test', 'role' => 'admin'],
        ['name' => 'Dewi Keuangan', 'email' => 'finance@kostmanagement.test', 'role' => 'finance'],
        ['name' => 'Agus Teknisi', 'email' => 'technician@kostmanagement.test', 'role' => 'technician'],
        ['name' => 'Rina Penghuni', 'email' => 'tenant@kostmanagement.test', 'role' => 'tenant'],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('DevUserSeeder skipped: not in local/testing environment.');

            return;
        }

        $password = Hash::make((string) env('KOST_DEV_PASSWORD', 'password'));

        foreach (self::USERS as $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => null,
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
