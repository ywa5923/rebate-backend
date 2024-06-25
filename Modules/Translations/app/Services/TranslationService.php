<?php

namespace Modules\Translations\Services;

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


    public function process(array $queryParams):?TranslationCollection
    {
        if (count($queryParams) == 0)
            return null;

        $queryBuilder = Translation::query();
        if (isset($queryParams["whereParams"]))
            foreach ($queryParams["whereParams"] as $param) {
                $queryBuilder->where(...$param);
            }

        if (isset($queryParams["whereInParams"])) {
            foreach ($queryParams["whereInParams"] as $param) {
                $queryBuilder->whereIn($param[0], $param[1]);
            }
        }

        if (isset($queryParams["orderBy"][0]) && isset($queryParams["orderDirection"]))
            $queryBuilder->orderBy($queryParams["orderBy"][0], $queryParams["orderDirection"]);


        return new TranslationCollection($queryBuilder->get());
    }
}
