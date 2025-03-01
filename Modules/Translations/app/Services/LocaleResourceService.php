<?php

namespace Modules\Translations\Services;

use App\Services\BaseQueryParser;
use Illuminate\Database\Eloquent\Collection;
use Modules\Translations\Models\LocaleResource;
use Modules\Translations\Repositories\TranslationRepository;
use Modules\Translations\Models\Translation;
use Modules\Translations\Transformers\LocaleResourceCollection;

class LocaleResourceService
{

   


    public function process(BaseQueryParser $queryParser):?LocaleResourceCollection
    {

        $queryBuilder = LocaleResource::query();
        
            foreach ($queryParser->getWhereParams() as $k=>$v) {
                $queryBuilder->where(...$v);
            }

        return new LocaleResourceCollection($queryBuilder->get());
    }
}

