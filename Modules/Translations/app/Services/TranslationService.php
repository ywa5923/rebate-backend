<?php
namespace Modules\Translations\Services;

use Modules\Translations\Repositories\TranslationRepository;

class TranslationService
{

    public function __construct(protected TranslationRepository $translationRep){}

    public function translatePropertyArray(string $fullClass, string $language, array $propertyArray)
    {
        return $this->translationRep->translatePropertyArray($fullClass, $language, $propertyArray);
    }

    public function translateTableColumns(string $fullClass,string $language)
    {
        return $this->translationRep->translateTableColumns($fullClass,$language);
    }
   
}

