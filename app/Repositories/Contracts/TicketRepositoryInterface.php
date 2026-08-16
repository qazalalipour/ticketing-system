<?php

namespace App\Repositories\Contracts;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TicketRepositoryInterface
{
    public function create(array $data): Ticket;
    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator;
    public function findForUser(User $user, int $id): ?Ticket;
    public function paginateForAdmin(User $admin, int $perPage = 10): LengthAwarePaginator;
    public function findForAdmin(int $id): ?Ticket;
    public function update(Ticket $ticket, array $data): Ticket;
    public function findManyForUpdate(array $ticketIds): Collection;
}
