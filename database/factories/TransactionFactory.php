<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'category_id' => $type === 'expense' ? Category::factory() : null,
            'type' => $type,
            'amount' => fake()->randomFloat(2, 1, 10000),
            'description' => fake()->sentence(),
            'transaction_date' => fake()->date(),
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category_id' => null,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category_id' => Category::factory(),
        ]);
    }
}