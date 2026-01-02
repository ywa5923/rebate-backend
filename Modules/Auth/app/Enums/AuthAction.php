<?php

namespace Modules\Auth\Enums;

enum AuthAction: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case MANAGE = 'manage';
}
