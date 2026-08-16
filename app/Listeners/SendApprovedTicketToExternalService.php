<?php

namespace App\Listeners;

use App\Events\TicketApproved;
use App\Jobs\SendTicketToExternalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApprovedTicketToExternalService
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
    public function handle(TicketApproved $event): void
    {
        SendTicketToExternalService::dispatch($event->ticket->id);
    }
}
