<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN_LEVEL_1 = 'admin_level_1';
    case ADMIN_LEVEL_2 = 'admin_level_2';
}
