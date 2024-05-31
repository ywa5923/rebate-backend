<?php

namespace Modules\Brokers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrokerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Brokers\Models\Broker::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            "logo"=>$this->faker->imageUrl(),
            "favicon"=>$this->faker->imageUrl(),
            "trading_name"=>$this->faker->company(),
            "broker_type_id"=>$this->faker->randomElement([1,2,3])
        ];

        
    }
}

