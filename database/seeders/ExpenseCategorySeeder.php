<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Reference data — seeded in every environment.
     */
    public function run(): void
    {
        $categories = [
            'Listrik & Air',
            'Perawatan',
            'Gaji',
            'Kebersihan',
            'Keamanan',
            'Pajak',
            'Renovasi',
            'Peralatan',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            ExpenseCategory::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true],
            );
        }
    }
}
