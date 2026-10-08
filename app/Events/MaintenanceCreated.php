<?php

namespace App\Events;

use App\Models\MaintenanceTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MaintenanceTicket $ticket) {}
}
