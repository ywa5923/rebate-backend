<?php
namespace Modules\Translations\Repositories;

enum TranslationType: string
{
    case COLUMNS = 'columns';
    case PROPERTY = 'property';
    case PROPERTIES = 'properties';

}
