<?php

namespace Database\Factories;

use App\Enums\InvoiceItemType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'type' => InvoiceItemType::Rent,
            'description' => 'Sewa',
            'quantity' => 1,
            'unit_price' => 1500000,
            'amount' => 1500000,
            'sort_order' => 0,
        ];
    }
}
