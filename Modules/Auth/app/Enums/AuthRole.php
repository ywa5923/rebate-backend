<?php

namespace Modules\Auth\Enums;

enum AuthRole: string
{
    case SUPER_ADMIN = 'super-admin';
    case COUNTRY_ADMIN = 'country_admin';
    case BROKER_ADMIN = 'broker_admin';
    case SEO = 'seo';
    case TRANSLATOR = 'translator';
}