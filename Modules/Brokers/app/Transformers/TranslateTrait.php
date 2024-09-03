<?php

namespace Modules\Brokers\Transformers;

trait TranslateTrait
{
    public function translate($field,$isOptionValueSlug=false)
    {

        //refactor this
        if($this->$field===null && $isOptionValueSlug===false)
        return null;
        $translations=$this->translations;

        foreach($translations as $translation)
        {
            if($translation->property==$field)
           return $translation->value;
        }
        return ($isOptionValueSlug)?$this->value:$this->$field;
    }

    public function translateBrokerOption($prop)
    {
        //for default language, translations relationship is not loaded, so just return the name
        if(!$this->relationLoaded('translations'))
        {
            return $this->name;
        }
     return ($this->getTranslatedProperty($prop))??$this->name;
       
    }

    public function getTranslatedProperty($prop)
    {
        foreach($this->translations as $translation)
        {
            if($translation->property==$prop)
           return $translation->value;
        }
    }
}