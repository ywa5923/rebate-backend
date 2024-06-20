<?php

namespace Modules\Translations\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Modules\Translations\Models\Translation;
use Modules\Translations\Repositories\TranslationType;

class TranslationRepository implements RepositoryInterface
{
    use TranslationTrait;


    public function translateTableColumns(string $fullClass, string $language): string
    {
        return Translation::where([
            ["translationable_type", $fullClass],
            ["translation_type", TranslationType::COLUMNS->value],
            ["language_code", $language]
        ])->get()->first()->metadata;
    }


    public function translatePropertyArray(string $fullClass, string $language, array $propertyArray):Collection
    {
        return  Translation::where(
            [
                ["translationable_type", $fullClass],
                ["translation_type", TranslationType::PROPERTY->value],
                ["language_code", $language]

            ]
        )->whereIn("property", $propertyArray)->get();
    }
}
