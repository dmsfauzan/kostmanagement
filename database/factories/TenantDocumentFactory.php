<?php

namespace Database\Factories;

use App\Enums\TenantDocumentType;
use App\Models\Tenant;
use App\Models\TenantDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantDocument>
 */
class TenantDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'type' => TenantDocumentType::Ktp,
            'number' => fake()->numerify('################'),
            'file_path' => 'tenant-documents/'.fake()->uuid().'.pdf',
            'expires_at' => null,
            'uploaded_by' => null,
        ];
    }
}
