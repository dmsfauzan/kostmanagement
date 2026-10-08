<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['subject_type', 'subject_id', 'key', 'sent_at'])]
class ReminderLog extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function alreadySent(Model $subject, string $key): bool
    {
        return static::query()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->where('key', $key)
            ->exists();
    }

    public static function markSent(Model $subject, string $key): void
    {
        static::query()->firstOrCreate(
            [
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'key' => $key,
            ],
            ['sent_at' => now()],
        );
    }
}
