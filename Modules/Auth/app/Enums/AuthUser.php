<?php

namespace Modules\Auth\Enums;

enum AuthUser: string
{
    case BROKER_TEAM_USER = 'broker_team_user';
    case PLATFORM_USER = 'platform_user';
}
