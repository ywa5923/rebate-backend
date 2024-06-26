<?php

namespace Modules\Brokers\Transformers;

trait TranslateTrait
{
    public function translate($field,$isOptionSlug=false)
    {
        $translations=$this->translations;

        foreach($translations as $translation)
        {
            if($translation->property==$field)
           return $translation->value;
        }
        return ($isOptionSlug)?$this->value:$this->$field;
    }
}