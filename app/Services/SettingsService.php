<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'kost.settings.all';

    /**
     * @return array<string, array{value: ?string, type: string}>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Setting::query()
                ->get(['key', 'value', 'type'])
                ->mapWithKeys(fn (Setting $setting): array => [
                    $setting->key => ['value' => $setting->value, 'type' => $setting->type],
                ])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        if (! array_key_exists($key, $settings)) {
            return $default;
        }

        return $this->cast($settings[$key]['value'], $settings[$key]['type']);
    }

    public function set(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'string',
        ?string $description = null,
        bool $isPublic = false,
    ): Setting {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $this->normalize($value, $type),
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
                'updated_by' => auth()->id(),
            ],
        );

        $this->flush();

        return $setting;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function cast(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int', 'integer' => (int) $value,
            'float', 'decimal' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    private function normalize(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'bool', 'boolean' => $value ? '1' : '0',
            'json', 'array' => json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }
}
