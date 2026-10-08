<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        if (Expense::query()->exists()) {
            return;
        }

        $property = Property::query()->first();

        if (! $property) {
            return;
        }

        $samples = [
            ['Listrik & Air', 850000, 'PLN & PDAM'],
            ['Perawatan', 300000, 'Servis AC'],
            ['Kebersihan', 400000, 'Jasa Kebersihan'],
            ['Gaji', 1500000, 'Petugas Kebersihan'],
        ];

        foreach ($samples as $index => [$categoryName, $amount, $vendor]) {
            $category = ExpenseCategory::query()->where('slug', Str::slug($categoryName))->first();

            Expense::query()->create([
                'property_id' => $property->id,
                'expense_category_id' => $category?->id,
                'amount' => $amount,
                'expense_date' => now()->subDays($index * 7)->toDateString(),
                'vendor' => $vendor,
                'description' => 'Data contoh pengeluaran.',
            ]);
        }

        $this->command?->info('ExpenseSeeder: sample expenses created.');
    }
}
