<?php

namespace Modules\Auth\Enums;

use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerGroup;
use Modules\Brokers\Models\Zone;
use Modules\Translations\Models\Country;

enum AuthPermission: string
{
    case BROKER = 'broker';
    case COUNTRY = 'country';
    case ZONE = 'zone';
    case SEO = 'seo';
    case TRANSLATOR = 'translator';
    case BROKER_GROUP = 'broker_group';

    public function resourceModel(): ?string
    {
        //this function returns the model class for the permission type
        //it is used to get the resource list for the permission type in UserPermissionForm
        return match ($this) {
            self::BROKER => Broker::class,
            self::COUNTRY, self::SEO, self::TRANSLATOR => Country::class,
            self::ZONE => Zone::class,
            self::BROKER_GROUP => BrokerGroup::class,
        };
    }
}
