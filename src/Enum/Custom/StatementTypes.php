<?php

namespace App\Enum\Custom;

/**
 * Перечисления для указания типа заявления
 */
final class StatementTypes extends Enumeration
{
    const array FIRST = [
        'id' => 1,
        'title' => 'Первичное'
    ];

    const array REPEATED = [
        'id' => 2,
        'title' => 'Повторное'
    ];
}
