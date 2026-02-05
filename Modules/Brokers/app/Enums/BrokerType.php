<?php
namespace Modules\Brokers\Enums;

enum BrokerType: string
{
    case BROKER = 'broker';
    case CRYPTO = 'crypto';
    case PROP_FIRM = 'prop_firm';
}