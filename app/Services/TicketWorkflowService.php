<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Events\TicketApproved;
use App\Events\TicketStatusChanged;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketWorkflowService
{
    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository
    ) {
    }

    public function approve(User $admin, Ticket $ticket): Ticket
    {
        $this->validateApproval($admin, $ticket);
        $fromStatus = $ticket->status->value;
        $nextStatus = match ($admin->role) {
            UserRole::ADMIN_LEVEL_1 => TicketStatus::PENDING_ADMIN_2,
            UserRole::ADMIN_LEVEL_2 => TicketStatus::APPROVED,
            default => throw ValidationException::withMessages([
                'admin' => 'You are not allowed to approve tickets.',
            ]),
        };
        $ticket = $this->ticketRepository->update($ticket, [
            'status' => $nextStatus,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
        TicketStatusChanged::dispatch($ticket, $fromStatus, $nextStatus->value, $admin->id);
        if ($nextStatus === TicketStatus::APPROVED) {
            TicketApproved::dispatch($ticket);
        }
        return $ticket;
    }

    public function reject(User $admin, Ticket $ticket, string $reason): Ticket
    {
        $this->validateRejection($admin, $ticket);
        $fromStatus = $ticket->status->value;
        $ticket = $this->ticketRepository->update($ticket, [
            'status' => TicketStatus::REJECTED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
        TicketStatusChanged::dispatch($ticket, $fromStatus, TicketStatus::REJECTED->value, $admin->id, $reason);
        return $ticket;
    }

    private function validateApproval(User $admin, Ticket $ticket): void
    {
        if (!$this->canManage($admin, $ticket)) {
            throw ValidationException::withMessages([
                'ticket' => 'This ticket cannot be approved by you.',
            ]);
        }
    }

    private function validateRejection(User $admin, Ticket $ticket): void
    {
        if (!$this->canManage($admin, $ticket)) {
            throw ValidationException::withMessages([
                'ticket' => 'This ticket cannot be rejected by you.',
            ]);
        }
    }

    private function canManage(User $admin, Ticket $ticket): bool
    {
        return match ($admin->role) {
            UserRole::ADMIN_LEVEL_1 => $ticket->status === TicketStatus::PENDING_ADMIN_1,
            UserRole::ADMIN_LEVEL_2 => $ticket->status === TicketStatus::PENDING_ADMIN_2,
            default => false,
        };
    }

    public function bulkApprove(User $admin, array $ticketIds): array
    {
        return DB::transaction(function () use ($admin, $ticketIds) {
            $tickets = $this->ticketRepository->findManyForUpdate($ticketIds);
            if ($tickets->count() !== count($ticketIds)) {
                throw ValidationException::withMessages([
                    'ticket_ids' => 'One or more tickets were not found.',
                ]);
            }
            foreach ($tickets as $ticket) {
                $this->validateApproval($admin, $ticket);
            }
            $approvedTickets = [];
            foreach ($tickets as $ticket) {
                $fromStatus = $ticket->status->value;
                $nextStatus = match ($admin->role) {
                    UserRole::ADMIN_LEVEL_1 => TicketStatus::PENDING_ADMIN_2,
                    UserRole::ADMIN_LEVEL_2 => TicketStatus::APPROVED,
                    default => throw ValidationException::withMessages([
                        'admin' => 'You are not allowed to approve tickets.',
                    ]),
                };
                $ticket = $this->ticketRepository->update($ticket, [
                        'status' => $nextStatus,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                        'rejection_reason' => null,
                    ]
                );
                TicketStatusChanged::dispatch($ticket, $fromStatus, $nextStatus->value, $admin->id);
                if ($nextStatus === TicketStatus::APPROVED) {
                    TicketApproved::dispatch($ticket);
                }
                $approvedTickets[] = $ticket;
            }
            return $approvedTickets;
        });
    }
}
