<?php

namespace App\Events;

use App\Models\MaintenanceTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MaintenanceTicket $ticket,
        public readonly string $fromStatus,
        public readonly string $toStatus,
    ) {}
}
