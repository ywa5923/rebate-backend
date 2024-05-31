<?php

namespace Modules\Translations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Brokers\Models\Broker;

class TranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Translations\Models\Translation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $language=$this->faker->randomElement(["en","fr","ro"]);

        

        $data= [
            'translationable_type'=>Broker::class,
            'translationable_id'=>Broker::factory(),
            'language_code'=>$language,
            'metadata'=>"\"{'trading_name':'{$language}-Lorem ipsum'}\"",
            'translation_type'=>'properties'
        ];

       return $data;
    }
}

