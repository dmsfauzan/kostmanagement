<?php

use App\Models\Setting;
use App\Services\SettingsService;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed(SettingsSeeder::class);
});

it('reads seeded settings with correct casting', function () {
    $settings = app(SettingsService::class);

    expect($settings->get('billing.due_days'))->toBe(10)
        ->and($settings->get('billing.prorate_enabled'))->toBeFalse()
        ->and($settings->get('reminder.days_before_due'))->toBe([7, 3, 1, 0])
        ->and($settings->get('app.currency'))->toBe('IDR');
});

it('returns the default when a key is missing', function () {
    expect(app(SettingsService::class)->get('missing.key', 'fallback'))->toBe('fallback');
});

it('writes and casts values, then flushes the cache', function () {
    $settings = app(SettingsService::class);

    $settings->set('billing.due_days', 20, 'billing', 'int');

    expect($settings->get('billing.due_days'))->toBe(20)
        ->and(Setting::query()->where('key', 'billing.due_days')->value('value'))->toBe('20');

    $settings->set('billing.due_days', 5, 'billing', 'int');

    expect(app(SettingsService::class)->get('billing.due_days'))->toBe(5);
});
