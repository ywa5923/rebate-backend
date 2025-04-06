<?php

namespace Modules\Translations\Services;

use App\Utilities\BaseQueryParser;
use Illuminate\Database\Eloquent\Collection;
use Modules\Translations\Repositories\TranslationRepository;
use Modules\Translations\Models\Translation;
use Modules\Translations\Transformers\TranslationCollection;

class TranslationService
{

    public function __construct(protected TranslationRepository $translationRep)
    {
    }

    public function translatePropertyArray(string $fullClass, string $language, array $propertyArray)
    {
        return $this->translationRep->translatePropertyArray($fullClass, $language, $propertyArray);
    }

    public function translateTableColumns(string $fullClass, string $language)
    {
        return $this->translationRep->translateTableColumns($fullClass, $language);
    }


    public function process(BaseQueryParser $queryParser):?TranslationCollection
    {

        //dd($queryParser->getWhereParams());
        // if (count($queryParser->getWhereParams()) == 0 || count($queryParser->getWhereInParams()) == 0)
        //     return null;

        $queryBuilder = Translation::query();
        
            foreach ($queryParser->getWhereParams() as $k=>$v) {
                $queryBuilder->where(...$v);
            }

       
            foreach ($queryParser->getWhereInParams() as $k=>$v) {
                $queryBuilder->whereIn($v[0], $v[1]);
            }
        

        if (!empty($queryParser->getOrderBy()) && !empty($queryParser->getOrderDirection()))
            $queryBuilder->orderBy($queryParser->getOrderBy()[0], $queryParser->getOrderDirection());


        return new TranslationCollection($queryBuilder->get());
    }
}
