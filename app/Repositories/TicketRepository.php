<?php

namespace App\Repositories;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TicketRepository implements TicketRepositoryInterface
{
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Ticket::query()->where('user_id', $user->id)->with('attachments')->latest()->paginate($perPage);
    }

    public function findForUser(User $user, int $id): ?Ticket
    {
        return Ticket::query()->where('user_id', $user->id)->with(['attachments', 'statusHistories.changedBy'])->find($id);
    }

    public function paginateForAdmin(User $admin, int $perPage = 10): LengthAwarePaginator
    {
        $query = Ticket::query()->with(['user', 'attachments',])->latest();
        if ($admin->role === UserRole::ADMIN_LEVEL_1) {
            $query->where('status', TicketStatus::PENDING_ADMIN_1);
        }
        if ($admin->role === UserRole::ADMIN_LEVEL_2) {
            $query->where('status', TicketStatus::PENDING_ADMIN_2);
        }
        return $query->paginate($perPage);
    }

    public function findForAdmin(int $id): ?Ticket
    {
        return Ticket::query()->with(['user', 'attachments', 'statusHistories.changedBy'])->find($id);
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);
        return $ticket->refresh();
    }

    public function findManyForUpdate(array $ticketIds): Collection
    {
        return Ticket::query()->whereIn('id', $ticketIds)->lockForUpdate()->get();
    }
}
