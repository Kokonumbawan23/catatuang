<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringTransactionFactory extends Factory
{
    protected $model = RecurringTransaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'title' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 1000, 1000000),
            'type' => fake()->randomElement(['income', 'expense']),
            'category_id' => Category::factory(),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'schedule_config' => ['interval_days' => 1],
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'is_active' => true,
            'requires_confirmation' => false,
            'last_executed_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function daily(array $config = []): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'daily',
            'schedule_config' => array_merge(['interval_days' => 1], $config),
        ]);
    }

    public function weekly(array $days = [1, 5]): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'weekly',
            'schedule_config' => ['day_of_week' => $days],
        ]);
    }

    public function monthly(int $dayOfMonth = 25, int $intervalMonths = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'monthly',
            'schedule_config' => [
                'day_of_month' => $dayOfMonth,
                'interval_months' => $intervalMonths,
            ],
        ]);
    }
}
