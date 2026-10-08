<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum MaintenanceStatus: string implements HasLabel
{
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case InProgress = 'in_progress';
    case WaitingVendor = 'waiting_vendor';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Diajukan',
            self::Acknowledged => 'Diterima',
            self::InProgress => 'Dikerjakan',
            self::WaitingVendor => 'Menunggu Vendor',
            self::Resolved => 'Selesai',
            self::Closed => 'Ditutup',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::Acknowledged => 'primary',
            self::InProgress => 'warning',
            self::WaitingVendor => 'warning',
            self::Resolved => 'success',
            self::Closed => 'neutral',
            self::Rejected => 'danger',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Resolved, self::Closed, self::Rejected], true);
    }

    /**
     * @return list<MaintenanceStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::Acknowledged, self::Rejected],
            self::Acknowledged => [self::InProgress, self::Rejected],
            self::InProgress => [self::WaitingVendor, self::Resolved],
            self::WaitingVendor => [self::InProgress, self::Resolved],
            self::Resolved => [self::Closed, self::InProgress],
            self::Closed => [self::InProgress],
            self::Rejected => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
