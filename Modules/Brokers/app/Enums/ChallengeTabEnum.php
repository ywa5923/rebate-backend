<?php

namespace Modules\Brokers\Enums;

enum ChallengeTabEnum: string
{
    case CATEGORY = 'category';
    case STEP = 'step';
    case AMOUNT = 'amount';

    /**
     * Return all enum string values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn(self $c) => $c->value, self::cases());
    }
}