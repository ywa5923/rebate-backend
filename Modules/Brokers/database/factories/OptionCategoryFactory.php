<?php

namespace Modules\Brokers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Brokers\Models\OptionCategory;

class OptionCategoryFactory extends Factory
{
    protected $model = OptionCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'position' => $this->faker->numberBetween(1, 10),
        ];
    }
}

