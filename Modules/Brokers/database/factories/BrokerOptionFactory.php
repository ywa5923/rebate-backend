<?php

namespace Modules\Brokers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Brokers\Models\BrokerOption;
use Modules\Brokers\Models\OptionCategory;
use Modules\Brokers\Models\DropdownCategory;

class BrokerOptionFactory extends Factory
{
    protected $model = BrokerOption::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'applicable_for' => $this->faker->randomElement(['broker', 'company', 'account_type']),
            'data_type' => $this->faker->randomElement(['text', 'string', 'number', 'boolean']),
            'form_type' => $this->faker->randomElement(['textarea', 'text', 'input', 'select']),
            'meta_data' => ['key' => 'value'],
            'for_crypto' => $this->faker->boolean(),
            'for_brokers' => $this->faker->boolean(),
            'for_props' => $this->faker->boolean(),
            'required' => $this->faker->boolean(),
            'placeholder' => $this->faker->sentence(),
            'tooltip' => $this->faker->sentence(),
            'min_constraint' => $this->faker->numberBetween(1, 10),
            'max_constraint' => $this->faker->numberBetween(10, 100),
            'load_in_dropdown' => $this->faker->boolean(),
            'default_loading' => $this->faker->boolean(),
            'default_loading_position' => $this->faker->numberBetween(1, 10),
            'dropdown_position' => $this->faker->numberBetween(1, 10),
            'category_position' => $this->faker->numberBetween(1, 10),
            'publish' => true,
            'position' => $this->faker->numberBetween(1, 10),
            'allow_sorting' => $this->faker->boolean(),
            'default_language' => 'en',
            'option_category_id' => OptionCategory::factory(),
            'dropdown_category_id' => null,
        ];
    }
}

