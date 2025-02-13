<?php

namespace App\Enum\Custom;

use App\Abstractions\Common\Enumeration;

/**
 * Перечисления для указания типа заявления
 */
final class StatementTypes extends Enumeration
{
    const FIRST = [
        'id' => 1,
        'title' => 'Первичное'
    ];

    const REPEATED = [
        'id' => 2,
        'title' => 'Повторное'
    ];
}
