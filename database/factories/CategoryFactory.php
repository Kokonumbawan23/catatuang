<?php

namespace Database\Factories;

use App\Enums\TransactionType;
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
            'type' => TransactionType::Expense,
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Expense Category',
            'type' => TransactionType::Expense,
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Income Category',
            'type' => TransactionType::Income,
        ]);
    }
}
