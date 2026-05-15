<?php

namespace App\Utilities;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property string $public_value
 * @property array $metadata
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Translations\Models\Translation> $translations
 *
 * @method bool relationLoaded(string $relation)
 */
trait TranslateTrait
{
    public function translate(string $field, bool $isOptionValueSlug = false): ?string
    {

        //refactor this
        if ($this->$field === null && $isOptionValueSlug === false) {
            return null;
        }

        if (! $this->relationLoaded('translations')) {
            return $this->$field;
        }

        $translations = $this->translations;

        foreach ($translations as $translation) {
            if ($translation->property == $field) {
                return $translation->value;
            }
        }

        //
        //when the translation is not found return the default value of the field
        //for  option_values the default value of the slug is $this->pubic_value
        return ($isOptionValueSlug) ? $this->public_value : $this->$field;
    }

    /**
     * Return the translated metadata for a given field
     */
    public function translateOptionMeta(string $field): ?array
    {

        // if($this->$field===null)
        // return null;

        foreach ($this->translations as $translation) {
            if ($translation->property == $field) {
                return $translation->metadata;
            }
        }

        //if the translation is not found,return null
        return $this->metadata;
    }

    public function translateOptionPublicValue(string $propSlug): ?string
    {
        ///for default language, translations relationship is not loaded, so just return the name
        if (! $this->relationLoaded('translations')) {
            return $this->public_value;
        }

        return $this->getTranslatedProperty($propSlug) ?? $this->public_value;
    }

    public function translateBrokerOption(string $prop): ?string
    {
        //this function is used to translate only options not option values

        //for default language, translations relationship is not loaded, so just return the name
        if (! $this->relationLoaded('translations')) {
            return $this->name;
        }

        return $this->getTranslatedProperty($prop) ?? $this->name;

    }

    public function translateProp(string $prop): ?string
    {

        if (! $this->relationLoaded('translations')) {
            return $this->{$prop};
        }

        return $this->getTranslatedProperty($prop) ?? $this->{$prop};

    }

    public function getTranslatedProperty(string $prop): ?string
    {
        foreach ($this->translations as $translation) {
            if ($translation->property == $prop) {
                return $translation->value;
            }
        }

        return null;
    }
}
