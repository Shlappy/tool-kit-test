<?php

namespace App\Enum;

/**
 * Перечисления существующих ключей для кеширования
 */
enum CacheKeys: string {
    case ADMIN_STATEMENT_LIST = 'admin_statement_list';
}