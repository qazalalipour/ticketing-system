<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkApproveTicketRequest;
use App\Http\Requests\RejectTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Services\TicketService;
use App\Services\TicketWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\TicketResource;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly TicketWorkflowService $workflowService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min($request->integer('per_page', 10), 50);
        if ($user->isAdmin()) {
            $tickets = $this->ticketService->paginateForAdmin($user, $perPage);
        } else {
            $tickets = $this->ticketService->paginateForUser($user, $perPage);
        }

        return TicketResource::collection($tickets);
    }

    public function show(Request $request, int $ticket)
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            $ticketModel = $this->ticketService->findForAdmin($ticket);
        } else {
            $ticketModel = $this->ticketService->findForUser($user, $ticket);
        }
        if (!$ticketModel) {
            abort(404);
        }
        Gate::authorize('view', $ticketModel);
        return new TicketResource($ticketModel);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->create(
            [
                ...$request->safe()->except('attachment'),
                'user_id' => $request->user()->id,
            ],
            $request->file('attachment')
        );
        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function approve(Request $request, int $ticket): JsonResponse
    {
        $ticketModel = $this->ticketService->findForAdmin($ticket);
        if (!$ticketModel) {
            abort(404);
        }
        Gate::authorize('approve', $ticketModel);
        $updatedTicket = $this->workflowService->approve($request->user(), $ticketModel);
        return response()->json([
            'message' => 'Ticket approved successfully.',
            'data' => new TicketResource($updatedTicket),
        ]);
    }

    public function reject(RejectTicketRequest $request, int $ticket): JsonResponse
    {
        $ticketModel = $this->ticketService->findForAdmin($ticket);
        if (!$ticketModel) {
            abort(404);
        }
        Gate::authorize('reject', $ticketModel);
        $updatedTicket = $this->workflowService->reject(
            $request->user(),
            $ticketModel,
            $request->string('reason')->toString()
        );
        return response()->json([
            'message' => 'Ticket rejected successfully.',
            'data' => new TicketResource($updatedTicket),
        ]);
    }

    public function bulkApprove(BulkApproveTicketRequest $request): JsonResponse
    {
        $tickets = $this->workflowService->bulkApprove($request->user(), $request->validated('ticket_ids'));
        return response()->json([
            'message' => 'Tickets approved successfully.',
            'data' => TicketResource::collection($tickets),
        ]);
    }
}
