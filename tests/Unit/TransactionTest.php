<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($user->id, $transaction->user->id);
    }

    public function test_transaction_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $transaction->category);
        $this->assertEquals($category->id, $transaction->category->id);
    }

    public function test_transaction_can_be_expense_type(): void
    {
        $transaction = Transaction::factory()->expense()->create();

        $this->assertEquals('expense', $transaction->type);
    }

    public function test_transaction_can_be_income_type(): void
    {
        $transaction = Transaction::factory()->income()->create();

        $this->assertEquals('income', $transaction->type);
    }

    public function test_incomes_scope_filters_income_transactions(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->count(3)->income()->create(['user_id' => $user->id]);
        Transaction::factory()->count(2)->expense()->create(['user_id' => $user->id]);

        $incomes = Transaction::forUser($user->id)->incomes()->get();

        $this->assertCount(3, $incomes);
        $this->assertTrue($incomes->every(fn ($t) => $t->type === 'income'));
    }

    public function test_expenses_scope_filters_expense_transactions(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->count(3)->income()->create(['user_id' => $user->id]);
        Transaction::factory()->count(2)->expense()->create(['user_id' => $user->id]);

        $expenses = Transaction::forUser($user->id)->expenses()->get();

        $this->assertCount(2, $expenses);
        $this->assertTrue($expenses->every(fn ($t) => $t->type === 'expense'));
    }

    public function test_for_month_scope_filters_by_month_and_year(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->create([
            'user_id' => $user->id,
            'transaction_date' => now(),
        ]);
        Transaction::factory()->create([
            'user_id' => $user->id,
            'transaction_date' => now()->subMonth(),
        ]);

        $currentMonthTransactions = Transaction::forUser($user->id)
            ->forMonth(now()->month, now()->year)
            ->get();

        $this->assertCount(1, $currentMonthTransactions);
    }

    public function test_for_user_scope_filters_by_user_id(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Transaction::factory()->count(3)->create(['user_id' => $user1->id]);
        Transaction::factory()->count(2)->create(['user_id' => $user2->id]);

        $user1Transactions = Transaction::forUser($user1->id)->get();

        $this->assertCount(3, $user1Transactions);
    }

    public function test_category_has_transactions_relationship(): void
    {
        $category = Category::factory()->create();
        Transaction::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->transactions);
        $this->assertInstanceOf(Transaction::class, $category->transactions->first());
    }

    public function test_user_has_transactions_relationship(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->transactions);
        $this->assertInstanceOf(Transaction::class, $user->transactions->first());
    }
}
