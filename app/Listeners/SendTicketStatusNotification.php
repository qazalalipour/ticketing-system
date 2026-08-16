<?php

namespace App\Listeners;

use App\Enums\TicketStatus;
use App\Events\TicketStatusChanged;
use App\Notifications\TicketStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTicketStatusNotification
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
        if (!in_array($event->toStatus, [TicketStatus::APPROVED->value, TicketStatus::REJECTED->value], true)) {
            return;
        }
        $event->ticket->user->notify(new TicketStatusNotification($event->ticket));
    }
}
