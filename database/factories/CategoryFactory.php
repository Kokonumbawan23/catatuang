<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'icon' => fake()->emoji(),
            'color' => fake()->hexColor(),
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Expense Category',
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Income Category',
        ]);
    }
}
