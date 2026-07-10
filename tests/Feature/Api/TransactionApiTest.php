<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        Transaction::factory()->create(['user_id' => $user->id, 'wallet_id' => $wallet->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'wallets',
                    'active_wallet',
                    'active_wallet_balance',
                    'total_income',
                    'total_expense',
                    'categories',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_list_transactions(): void
    {
        $response = $this->getJson('/api/transactions');

        $response->assertStatus(401);
    }

    public function test_user_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Makan siang',
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Transaksi berhasil dicatat.',
            ]);

        $wallet->refresh();
        $this->assertEquals(-50000, $wallet->balance);
    }

    public function test_income_transaction_increases_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 1000000,
            'description' => 'Gaji',
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);

        $wallet->refresh();
        $this->assertEquals(1000000, $wallet->balance);
    }

    public function test_user_cannot_create_transaction_with_other_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        $category = Category::factory()->create();
        Sanctum::actingAs($userA);

        $response = $this->postJson('/api/transactions', [
            'wallet_id' => $walletB->id,
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Test',
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Dompet tidak valid.',
            ]);
    }

    public function test_user_can_update_own_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
        ]);
        $wallet->update(['balance' => -50000]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/transactions/'.$transaction->id, [
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 75000,
            'description' => 'Updated',
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transaksi berhasil diperbarui.',
            ]);

        $wallet->refresh();
        $this->assertEquals(75000, $wallet->balance);
    }

    public function test_user_cannot_update_other_users_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletA = Wallet::factory()->create(['user_id' => $userA->id]);
        $category = Category::factory()->create();
        $transactionB = Transaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletA->id,
        ]);
        Sanctum::actingAs($userA);

        $response = $this->putJson('/api/transactions/'.$transactionB->id, [
            'wallet_id' => $walletA->id,
            'type' => 'income',
            'amount' => 999999,
            'description' => 'Hacked',
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 50000,
        ]);
        $wallet->update(['balance' => -50000]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/transactions/'.$transaction->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transaksi berhasil dihapus.',
            ]);

        $wallet->refresh();
        $this->assertEquals(0, $wallet->balance);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_delete_other_users_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        $transactionB = Transaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB->id,
        ]);
        Sanctum::actingAs($userA);

        $response = $this->deleteJson('/api/transactions/'.$transactionB->id);

        $response->assertStatus(403);
    }

    public function test_transaction_requires_valid_type(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'invalid',
            'amount' => 50000,
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_transaction_requires_positive_amount(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => -50000,
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
