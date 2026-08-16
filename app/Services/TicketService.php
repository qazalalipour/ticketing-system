<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository
    ) {
    }

    public function create(array $data, UploadedFile $attachment): Ticket
    {
        return DB::transaction(function () use ($data, $attachment) {
            $data['status'] = TicketStatus::PENDING_ADMIN_1;
            $ticket = $this->ticketRepository->create($data);
            $path = $attachment->store('tickets/' . $ticket->id, 'public');
            $ticket->attachments()->create([
                'file_path' => $path,
                'original_name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
            ]);
            return $ticket->load('attachments');
        });
    }

    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $this->ticketRepository->paginateForUser($user, $perPage);
    }

    public function findForUser(User $user, int $id): ?Ticket
    {
        return $this->ticketRepository->findForUser($user, $id);
    }

    public function paginateForAdmin(User $admin, int $perPage = 10): LengthAwarePaginator
    {
        return $this->ticketRepository->paginateForAdmin($admin, $perPage);
    }

    public function findForAdmin(int $id): ?Ticket
    {
        return $this->ticketRepository->findForAdmin($id);
    }
}
