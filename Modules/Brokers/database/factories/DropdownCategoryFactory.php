<?php

namespace Modules\Brokers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Brokers\Models\DropdownCategory;

class DropdownCategoryFactory extends Factory
{
    protected $model = DropdownCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
        ];
    }
}

