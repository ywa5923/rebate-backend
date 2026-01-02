<?php

namespace Modules\Auth\Enums;

use Modules\Brokers\Models\Broker;
use Modules\Translations\Models\Country;
use Modules\Brokers\Models\Zone;

enum AuthPermission: string
{
    case BROKER = 'broker';
    case COUNTRY = 'country';
    case ZONE = 'zone';
    case SEO = 'seo';
    case TRANSLATOR = 'translator';

    public function resourceModel(): ?string
    {
        return match ($this) {
            self::BROKER => Broker::class,
            self::COUNTRY, self::SEO, self::TRANSLATOR => Country::class,
            self::ZONE => Zone::class,
        };
    }
}
