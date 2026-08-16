<?php

namespace App\Services\External;

use App\Models\Ticket;
use App\Services\External\Contracts\TicketSenderInterface;
use Illuminate\Support\Facades\Http;

class FakeTicketSender implements TicketSenderInterface
{
    public function send(Ticket $ticket): bool
    {
        $response = Http::post(config('services.ticketing.url'),
            [
                'ticket_id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
            ]
        );

        return $response->successful();
    }
}
