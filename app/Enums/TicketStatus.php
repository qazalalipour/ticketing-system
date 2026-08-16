<?php

namespace App\Enums;

enum TicketStatus: string
{
    case PENDING_ADMIN_1 = 'pending_admin_1';
    case PENDING_ADMIN_2 = 'pending_admin_2';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
