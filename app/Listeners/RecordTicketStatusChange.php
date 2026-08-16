<?php

namespace App\Listeners;

use App\Events\TicketStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RecordTicketStatusChange
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TicketStatusChanged $event): void
    {
        $event->ticket->statusHistories()->create([
            'changed_by' => $event->changedBy,
            'from_status' => $event->fromStatus,
            'to_status' => $event->toStatus,
            'reason' => $event->reason,
        ]);
    }
}
