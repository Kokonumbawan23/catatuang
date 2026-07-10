<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $expenseCategory;

    private Category $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->expenseCategory = Category::factory()->expense()->create();
        $this->incomeCategory = Category::factory()->income()->create();
    }

    public function test_user_can_view_transaction_index(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Transaksi');
    }

    public function test_user_can_create_expense_transaction(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => 50000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Test expense',
        ]);

        $response->assertRedirectContains('transactions');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 50000,
        ]);
    }

    public function test_user_can_create_income_transaction(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000000,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Test income',
        ]);

        $response->assertRedirectContains('transactions');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 1000000,
        ]);
    }

    public function test_user_can_update_transaction(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 100000,
        ]);

        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 50000,
        ]);

        $response = $this->patch(route('transactions.update', $transaction), [
            'wallet_id' => $wallet->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 75000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirectContains('transactions');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 75000,
        ]);
    }

    public function test_user_can_delete_transaction(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 100000,
        ]);

        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
        ]);

        $response = $this->delete(route('transactions.destroy', $transaction));

        $response->assertRedirectContains('transactions');
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_access_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherWallet = $otherUser->wallets()->create([
            'name' => 'Other Wallet',
            'balance' => 0,
        ]);
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'wallet_id' => $otherWallet->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('transactions.edit', $transaction));

        $response->assertStatus(403);
    }

    public function test_transaction_requires_type(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 50000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_transaction_requires_valid_type(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'InvalidType',
            'category_id' => $this->expenseCategory->id,
            'amount' => 50000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_transaction_requires_positive_amount(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => -50000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_transaction_requires_minimum_amount_of_one(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => 0,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_transaction_requires_valid_category(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'category_id' => 99999,
            'amount' => 50000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_transaction_requires_transaction_date(): void
    {
        $wallet = $this->user->wallets()->create([
            'name' => 'Test Wallet',
            'balance' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'amount' => 50000,
        ]);

        $response->assertSessionHasErrors('transaction_date');
    }
}
