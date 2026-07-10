<?php

namespace Tests\Feature\Api;

use App\Models\RecurringTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecurringTransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_recurring_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        RecurringTransaction::factory()->create(['user_id' => $user->id, 'wallet_id' => $wallet->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/recurring-transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'amount',
                            'type',
                            'frequency',
                            'is_active',
                        ],
                    ],
                ],
                'meta' => [
                    'categories',
                    'wallets',
                    'total_active_this_month',
                    'monthly_commitment',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_list_recurring_transactions(): void
    {
        $response = $this->getJson('/api/recurring-transactions');

        $response->assertStatus(401);
    }

    public function test_user_can_create_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/recurring-transactions', [
            'wallet_id' => $wallet->id,
            'title' => 'Langganan Netflix',
            'amount' => 150000,
            'type' => 'expense',
            'category_id' => null,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => 15, 'interval_months' => 1],
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'is_active' => true,
            'requires_confirmation' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Transaksi berulang berhasil dibuat.',
            ]);

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'title' => 'Langganan Netflix',
        ]);
    }

    public function test_user_can_update_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'title' => 'Original Title',
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/recurring-transactions/'.$recurring->id, [
            'wallet_id' => $wallet->id,
            'title' => 'Updated Title',
            'amount' => 200000,
            'type' => 'income',
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 1],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transaksi berulang berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_other_users_recurring_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletA = Wallet::factory()->create(['user_id' => $userA->id]);
        $recurringB = RecurringTransaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletA->id,
        ]);
        Sanctum::actingAs($userA);

        $response = $this->putJson('/api/recurring-transactions/'.$recurringB->id, [
            'wallet_id' => $walletA->id,
            'title' => 'Hacked Title',
            'amount' => 999999,
            'type' => 'income',
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 1],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/recurring-transactions/'.$recurring->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transaksi berulang berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('recurring_transactions', [
            'id' => $recurring->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_recurring_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        $recurringB = RecurringTransaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB->id,
        ]);
        Sanctum::actingAs($userA);

        $response = $this->deleteJson('/api/recurring-transactions/'.$recurringB->id);

        $response->assertStatus(403);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurringB->id,
        ]);
    }

    public function test_user_can_toggle_recurring_transaction_active_status(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'is_active' => true,
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/recurring-transactions/'.$recurring->id.'/toggle');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['is_active'],
            ]);

        $recurring->refresh();
        $this->assertFalse($recurring->is_active);
    }

    public function test_user_cannot_toggle_other_users_recurring_transaction(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        $recurringB = RecurringTransaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB->id,
            'is_active' => true,
        ]);
        Sanctum::actingAs($userA);

        $response = $this->patchJson('/api/recurring-transactions/'.$recurringB->id.'/toggle');

        $response->assertStatus(403);
    }

    public function test_recurring_transaction_requires_valid_frequency(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/recurring-transactions', [
            'wallet_id' => $wallet->id,
            'title' => 'Test',
            'amount' => 50000,
            'type' => 'expense',
            'frequency' => 'invalid',
            'schedule_config' => ['interval_days' => 1],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency']);
    }

    public function test_recurring_transaction_validates_schedule_config(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/recurring-transactions', [
            'wallet_id' => $wallet->id,
            'title' => 'Test',
            'amount' => 50000,
            'type' => 'expense',
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => 32],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['schedule_config']);
    }

    public function test_only_shows_users_own_recurring_transactions(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletA = Wallet::factory()->create(['user_id' => $userA->id]);
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        RecurringTransaction::factory()->create([
            'user_id' => $userA->id,
            'wallet_id' => $walletA->id,
            'title' => 'User A Recurring',
        ]);
        RecurringTransaction::factory()->create([
            'user_id' => $userB->id,
            'wallet_id' => $walletB->id,
            'title' => 'User B Recurring',
        ]);
        Sanctum::actingAs($userA);

        $response = $this->getJson('/api/recurring-transactions');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('User A Recurring', $response->json('data.data.0.title'));
    }
}
