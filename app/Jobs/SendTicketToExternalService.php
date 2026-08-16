<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\External\Contracts\TicketSenderInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTicketToExternalService implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public function __construct(
        public readonly int $ticketId
    ) {
    }

    public function backoff(): int
    {
        return 3600;
    }

    public function handle(TicketSenderInterface $sender): void
    {
        $ticket = Ticket::findOrFail($this->ticketId);
        $success = $sender->send($ticket);
        if (!$success) {
            throw new \RuntimeException('Failed to send ticket to external service.');
        }
        Log::info('Ticket sent successfully.', ['ticket_id' => $ticket->id]);
    }
}
