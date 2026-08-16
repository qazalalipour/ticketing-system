<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->role === UserRole::USER) {
            return $ticket->user_id === $user->id;
        }
        return in_array($user->role, [
            UserRole::ADMIN_LEVEL_1,
            UserRole::ADMIN_LEVEL_2,
        ], true);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::USER;
    }

    public function approve(User $user, Ticket $ticket): bool
    {
        if ($user->role === UserRole::ADMIN_LEVEL_1) {
            return $ticket->status === TicketStatus::PENDING_ADMIN_1;
        }
        if ($user->role === UserRole::ADMIN_LEVEL_2) {
            return $ticket->status === TicketStatus::PENDING_ADMIN_2;
        }
        return false;
    }

    public function reject(User $user, Ticket $ticket): bool
    {
        if ($user->role === UserRole::ADMIN_LEVEL_1) {
            return $ticket->status === TicketStatus::PENDING_ADMIN_1;
        }
        if ($user->role === UserRole::ADMIN_LEVEL_2) {
            return $ticket->status === TicketStatus::PENDING_ADMIN_2;
        }
        return false;
    }
}
