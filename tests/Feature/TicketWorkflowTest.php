<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_level_one_can_approve_ticket(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN_LEVEL_1,
        ]);
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::PENDING_ADMIN_1,
        ]);
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/approve");
        $response->assertOk();
        $response->assertJsonPath(
            'data.status',
            TicketStatus::PENDING_ADMIN_2->value
        );
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::PENDING_ADMIN_2->value,
            'reviewed_by' => $admin->id,
        ]);
    }
}
