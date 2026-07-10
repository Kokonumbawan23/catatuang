<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_other_users_wallets(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletA = Wallet::factory()->create(['user_id' => $userA->id, 'name' => 'Wallet A']);
        $walletB = Wallet::factory()->create(['user_id' => $userB->id, 'name' => 'Wallet B']);

        $this->actingAs($userA);

        $response = $this->get(route('transactions.index'));

        $response->assertSee('Wallet A');
        $response->assertDontSee('Wallet B');
    }

    public function test_user_cannot_access_other_users_wallet_transactions(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletA = Wallet::factory()->create(['user_id' => $userA->id, 'balance' => 1000]);
        $walletB = Wallet::factory()->create(['user_id' => $userB->id, 'balance' => 500]);

        Transaction::factory()->create([
            'user_id' => $userA->id,
            'wallet_id' => $walletA->id,
            'type' => 'income',
            'amount' => 500,
            'description' => 'User A Transaction',
            'transaction_date' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB->id,
            'type' => 'expense',
            'amount' => 200,
            'description' => 'User B Transaction',
            'transaction_date' => now(),
        ]);

        $this->actingAs($userA);

        $response = $this->get(route('transactions.index', ['wallet_id' => $walletA->id]));

        $response->assertSee('User A Transaction');
        $response->assertSee('500');

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $userB->id,
            'wallet_id' => $walletA->id,
        ]);
    }

    public function test_user_cannot_create_transaction_with_other_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletB = Wallet::factory()->create(['user_id' => $userB->id, 'balance' => 500]);

        $this->actingAs($userA);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $walletB->id,
            'type' => 'income',
            'amount' => 100,
            'description' => 'Unauthorized transaction',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('wallet_id');
        $this->assertDatabaseMissing('transactions', [
            'wallet_id' => $walletB->id,
            'description' => 'Unauthorized transaction',
        ]);
    }

    public function test_user_cannot_update_other_users_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);

        $transactionB = Transaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB->id,
            'description' => 'Original description',
        ]);

        $this->actingAs($userA);

        $response = $this->put(route('transactions.update', $transactionB), [
            'wallet_id' => $walletB->id,
            'type' => 'income',
            'amount' => 999,
            'description' => 'Hacked description',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionB->id,
            'description' => 'Original description',
        ]);
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

        $this->actingAs($userA);

        $response = $this->delete(route('transactions.destroy', $transactionB));

        $response->assertStatus(403);
        $this->assertDatabaseHas('transactions', ['id' => $transactionB->id]);
    }

    public function test_transaction_amount_must_be_greater_than_zero(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 0,
            'description' => 'Zero amount',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_transaction_amount_cannot_be_negative(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => -100,
            'description' => 'Negative amount',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_user_cannot_access_wallet_balance_of_other_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Wallet::factory()->create(['user_id' => $userA->id, 'name' => 'Primary Wallet', 'balance' => 1000]);
        Wallet::factory()->create(['user_id' => $userB->id, 'name' => 'Secondary Wallet', 'balance' => 500]);

        $this->actingAs($userA);

        $response = $this->get(route('transactions.index'));

        $response->assertSee('Primary Wallet');
        $response->assertDontSee('Secondary Wallet');

        $response->assertSee('1.000');
    }

    public function test_wallet_isolation_when_switching_wallet_context(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $walletA1 = Wallet::factory()->create(['user_id' => $userA->id, 'name' => 'Main Wallet', 'balance' => 1000]);
        $walletA2 = Wallet::factory()->create(['user_id' => $userA->id, 'name' => 'Secondary Wallet', 'balance' => 2000]);
        $walletB1 = Wallet::factory()->create(['user_id' => $userB->id, 'name' => 'Private Wallet', 'balance' => 500]);

        Transaction::factory()->create([
            'user_id' => $userA->id,
            'wallet_id' => $walletA1->id,
            'type' => 'income',
            'amount' => 100,
            'description' => 'Income for Main Wallet',
            'transaction_date' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB1->id,
            'type' => 'expense',
            'amount' => 50,
            'description' => 'Private Expense',
            'transaction_date' => now(),
        ]);

        $this->actingAs($userA);

        $responseA1 = $this->get(route('transactions.index', ['wallet_id' => $walletA1->id]));
        $responseA1->assertSee('Main Wallet');
        $responseA1->assertSee('100');
        $responseA1->assertSee('1.000');
        $responseA1->assertSee('Income for Main Wallet');
        $responseA1->assertDontSee('Private Expense');

        $responseA2 = $this->get(route('transactions.index', ['wallet_id' => $walletA2->id]));
        $responseA2->assertSee('Secondary Wallet');
        $responseA2->assertSee('2.000');
        $responseA2->assertDontSee('Private Wallet');
        $responseA2->assertDontSee('Private Expense');
    }
}
