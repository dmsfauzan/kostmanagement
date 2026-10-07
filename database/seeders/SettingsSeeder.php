<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * @var array<string, array{value: string, group: string, type: string, description: string, is_public: bool}>
     */
    public const SETTINGS = [
        'app.name' => ['value' => 'Kost Management', 'group' => 'general', 'type' => 'string', 'description' => 'Application display name', 'is_public' => true],
        'app.timezone' => ['value' => 'Asia/Jakarta', 'group' => 'general', 'type' => 'string', 'description' => 'Application timezone', 'is_public' => false],
        'app.currency' => ['value' => 'IDR', 'group' => 'general', 'type' => 'string', 'description' => 'Currency code', 'is_public' => true],
        'app.currency_symbol' => ['value' => 'Rp', 'group' => 'general', 'type' => 'string', 'description' => 'Currency symbol', 'is_public' => true],

        'billing.prefix' => ['value' => 'INV', 'group' => 'billing', 'type' => 'string', 'description' => 'Invoice number prefix', 'is_public' => false],
        'billing.due_days' => ['value' => '10', 'group' => 'billing', 'type' => 'int', 'description' => 'Days after issue_date when invoice is due', 'is_public' => false],
        'billing.late_fee_type' => ['value' => 'none', 'group' => 'billing', 'type' => 'string', 'description' => 'Late-fee type: none, fixed_daily, percentage', 'is_public' => false],
        'billing.late_fee_value' => ['value' => '0', 'group' => 'billing', 'type' => 'int', 'description' => 'Late-fee value (rupiah or percent×100, e.g. 500 = 5.00%)', 'is_public' => false],
        'billing.prorate_enabled' => ['value' => '0', 'group' => 'billing', 'type' => 'boolean', 'description' => 'Enable prorated billing for mid-period move-ins', 'is_public' => false],
        'billing.prorate_basis_days' => ['value' => '30', 'group' => 'billing', 'type' => 'int', 'description' => 'Day divisor when calculating proration', 'is_public' => false],

        'reminder.days_before_due' => ['value' => '[7,3,1,0]', 'group' => 'reminder', 'type' => 'json', 'description' => 'Days before due date to send payment reminders (JSON array)', 'is_public' => false],
        'reminder.overdue_days' => ['value' => '[1,3,7]', 'group' => 'reminder', 'type' => 'json', 'description' => 'Days after due date to send overdue notices', 'is_public' => false],
        'reminder.lease_expiry_days' => ['value' => '[90,30,14,7]', 'group' => 'reminder', 'type' => 'json', 'description' => 'Days before lease end to notify expiry', 'is_public' => false],

        'maintenance.sla_hours' => ['value' => '48', 'group' => 'maintenance', 'type' => 'int', 'description' => 'Default maintenance SLA window (hours)', 'is_public' => false],

        'upload.max_size_kb' => ['value' => '4096', 'group' => 'upload', 'type' => 'int', 'description' => 'Maximum file size in kilobytes for all uploads', 'is_public' => true],
        'upload.allowed_mimes' => ['value' => 'jpg,jpeg,png,webp,pdf', 'group' => 'upload', 'type' => 'string', 'description' => 'Comma-separated list of allowed MIME extensions', 'is_public' => false],
    ];

    public function run(): void
    {
        foreach (self::SETTINGS as $key => $data) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => $data['group'],
                    'value' => $data['value'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'is_public' => $data['is_public'],
                ],
            );
        }
    }
}
