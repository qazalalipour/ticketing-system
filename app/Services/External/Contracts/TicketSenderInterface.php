<?php

namespace App\Services\External\Contracts;

use App\Models\Ticket;

interface TicketSenderInterface
{
    public function send(Ticket $ticket): bool;
}
