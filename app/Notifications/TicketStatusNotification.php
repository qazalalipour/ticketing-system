<?php

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Ticket $ticket
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = match ($this->ticket->status) {
            TicketStatus::APPROVED => 'Your ticket has been approved.',
            TicketStatus::REJECTED => 'Your ticket has been rejected.',
            default => 'The status of your ticket has changed.',
        };

        return (new MailMessage)
            ->subject('Ticket Status Update')
            ->greeting("Hello {$notifiable->name}")
            ->line($message)
            ->line("Ticket: {$this->ticket->title}")
            ->when(
                $this->ticket->rejection_reason,
                fn($mail) => $mail->line(
                    "Reason: {$this->ticket->rejection_reason}"
                )
            );
    }
}
