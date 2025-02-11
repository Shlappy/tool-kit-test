<?php

namespace App\Enum;

/**
 * Перечисления для указания существующих ролей в приложении
 */
enum Roles: string {
    case ADMIN = 'ROLE_ADMIN';
    case CLIENT = 'ROLE_CLIENT';
    case USER = 'ROLE_USER';
}