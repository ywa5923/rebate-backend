<?php

namespace App\Utilities;

class ModelHelper
{
    public static function getModelClassFromSlug($slug, $namespace = 'Modules\\Brokers\\Models\\')
    {
        $class = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $slug)));
        return $namespace . $class;
    }
}
