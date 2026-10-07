<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;

it('records an audit entry with actor and request context', function () {
    $actor = User::factory()->create();

    $this->actingAs($actor);

    $log = app(AuditService::class)->record(
        action: 'payment.verified',
        oldValues: ['status' => 'pending'],
        newValues: ['status' => 'verified'],
        module: 'Payment',
    );

    expect($log)->toBeInstanceOf(AuditLog::class)
        ->and($log->actor_id)->toBe($actor->id)
        ->and($log->action)->toBe('payment.verified')
        ->and($log->old_values)->toBe(['status' => 'pending'])
        ->and($log->new_values)->toBe(['status' => 'verified']);
});

it('refuses to update an audit entry', function () {
    $log = AuditLog::query()->create([
        'action' => 'room.updated',
        'created_at' => now(),
    ]);

    expect(fn () => $log->update(['action' => 'tampered']))
        ->toThrow(RuntimeException::class);
});

it('refuses to delete an audit entry', function () {
    $log = AuditLog::query()->create([
        'action' => 'room.updated',
        'created_at' => now(),
    ]);

    expect(fn () => $log->delete())
        ->toThrow(RuntimeException::class);
});
