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
            if($translation->property==$field )
           return $translation->value;
        }

        //when the translation is not found return the default value of the field
        //for  option_values the default value of the slug is $this->value
        return ($isOptionValueSlug)?$this->value:$this->$field;
    }


    /**
     * Return the translated metadata for a given field
     * 
     * @param string $field
     * @return array|null
     */

    public function translateOptionMeta($field)
    {
        
        // if($this->$field===null)
        // return null;
       

        foreach($this->translations as $translation)
        {
            if($translation->property==$field )
           return $translation->metadata;
        }
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

    public function translateProp($prop){

      
       
        if($this->translations->isEmpty()){
            return $this->{$prop};
         }
      
        return ($this->getTranslatedProperty($prop))??$this->{$prop};
        
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