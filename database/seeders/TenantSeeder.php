<?php

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Enums\UserStatus;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeaseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('TenantSeeder skipped: not in local/testing environment.');

            return;
        }

        $room = Room::query()
            ->where('status', RoomStatus::Available->value)
            ->with('property')
            ->first();

        if (! $room) {
            $this->command?->warn('TenantSeeder skipped: no available room.');

            return;
        }

        $tenantAccount = User::query()->updateOrCreate(
            ['email' => 'tenant@kostmanagement.test'],
            [
                'name' => 'Rina Penghuni',
                'phone' => '081212121212',
                'password' => Hash::make((string) env('KOST_DEV_PASSWORD', 'password')),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
        $tenantAccount->syncRoles(['tenant']);

        $tenant = Tenant::query()->firstOrCreate(
            ['email' => 'tenant@kostmanagement.test'],
            [
                'property_id' => $room->property_id,
                'user_id' => $tenantAccount->id,
                'full_name' => 'Rina Penghuni',
                'nik' => '3175010101960001',
                'gender' => 'female',
                'birth_date' => '1996-01-01',
                'phone' => '081212121212',
                'address' => 'Jl. Kenanga No. 8, Jakarta Selatan',
                'emergency_name' => 'Ahmad Sudirman',
                'emergency_relationship' => 'Saudara',
                'emergency_phone' => '081298989898',
                'vehicle_type' => 'Motor',
                'vehicle_number' => 'B 4321 XYZ',
                'status' => TenantStatus::Active,
            ],
        );

        $tenant->update([
            'property_id' => $room->property_id,
            'user_id' => $tenantAccount->id,
            'status' => TenantStatus::Active,
        ]);

        if (! $tenant->leases()->whereIn('status', ['active', 'expiring'])->exists()) {
            app(LeaseService::class)->create([
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'property_id' => $room->property_id,
                'start_date' => now()->firstOfMonth()->toDateString(),
                'end_date' => now()->firstOfMonth()->addYear()->subDay()->toDateString(),
                'rent_amount' => $room->price,
                'deposit_amount' => $room->deposit,
                'billing_cycle' => 'monthly',
                'due_day' => 5,
                'late_fee_type' => 'none',
                'late_fee_value' => 0,
                'notes' => 'Data contoh untuk development.',
            ]);

            $draft = $tenant->leases()->where('status', 'draft')->latest()->first();

            if ($draft) {
                app(LeaseService::class)->activate($draft);
            }
        }

        Tenant::factory()->count(2)->create(['property_id' => $room->property_id]);
    }
}
