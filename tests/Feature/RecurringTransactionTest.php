<?php

namespace Tests\Feature;

use App\Models\RecurringTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RecurringTransactionScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);

        $this->actingAs($user);

        $response = $this->post(route('recurring-transactions.store'), [
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

        $response->assertRedirect(route('recurring-transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'title' => 'Langganan Netflix',
            'frequency' => 'monthly',
        ]);
    }

    public function test_user_cannot_create_recurring_with_other_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);

        $this->actingAs($userA);

        $response = $this->post(route('recurring-transactions.store'), [
            'wallet_id' => $walletB->id,
            'title' => 'Test',
            'amount' => 100000,
            'type' => 'expense',
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 1],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('wallet_id');
    }

    public function test_schedule_config_validation_for_daily(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('recurring-transactions.store'), [
            'wallet_id' => $wallet->id,
            'title' => 'Test Daily',
            'amount' => 50000,
            'type' => 'expense',
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 0],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('schedule_config');
    }

    public function test_schedule_config_validation_for_weekly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('recurring-transactions.store'), [
            'wallet_id' => $wallet->id,
            'title' => 'Test Weekly',
            'amount' => 50000,
            'type' => 'expense',
            'frequency' => 'weekly',
            'schedule_config' => ['day_of_week' => [1, 6]],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_schedule_config_validation_for_monthly(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('recurring-transactions.store'), [
            'wallet_id' => $wallet->id,
            'title' => 'Test Monthly',
            'amount' => 50000,
            'type' => 'expense',
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => 32],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('schedule_config');
    }

    public function test_user_can_update_own_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'title' => 'Original Title',
            'amount' => 100000,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('recurring-transactions.update', $recurring), [
            'wallet_id' => $wallet->id,
            'title' => 'Updated Title',
            'amount' => 200000,
            'type' => 'income',
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 2],
            'start_date' => $recurring->start_date->toDateString(),
        ]);

        $response->assertRedirect(route('recurring-transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'title' => 'Updated Title',
            'amount' => 200000,
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
            'title' => 'User B Recurring',
        ]);

        $this->actingAs($userA);

        $response = $this->put(route('recurring-transactions.update', $recurringB), [
            'wallet_id' => $walletA->id,
            'title' => 'Hacked Title',
            'amount' => 999999,
            'type' => 'income',
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 1],
            'start_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurringB->id,
            'title' => 'User B Recurring',
        ]);
    }

    public function test_user_can_delete_own_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('recurring-transactions.destroy', $recurring));

        $response->assertRedirect(route('recurring-transactions.index'));
        $response->assertSessionHas('success');

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

        $this->actingAs($userA);

        $response = $this->delete(route('recurring-transactions.destroy', $recurringB));

        $response->assertStatus(403);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurringB->id,
        ]);
    }

    public function test_user_can_only_see_own_recurring_transactions(): void
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

        $this->actingAs($userA);

        $response = $this->get(route('recurring-transactions.index'));

        $response->assertSee('User A Recurring');
        $response->assertDontSee('User B Recurring');
    }

    public function test_recurring_service_creates_transaction_for_monthly_schedule(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $today = now()->day;

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'title' => 'Gaji Bulanan',
            'type' => 'income',
            'amount' => 500000,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => $today, 'interval_months' => 1],
            'is_active' => true,
            'start_date' => now()->subMonth(),
            'last_executed_at' => null,
        ]);

        $service = app(RecurringTransactionScheduleService::class);
        $transaction = $service->execute($recurring);

        $this->assertNotNull($transaction);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($wallet->id, $transaction->wallet_id);
        $this->assertEquals(500000, $transaction->amount);
        $this->assertEquals('income', $transaction->type);
        $this->assertEquals('Gaji Bulanan (Otomatis)', $transaction->description);

        $wallet->refresh();
        $this->assertEquals(500000, $wallet->balance);

        $recurring->refresh();
        $this->assertNotNull($recurring->last_executed_at);
    }

    public function test_recurring_service_does_not_duplicate_on_same_day(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $today = now()->day;

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 100000,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => $today, 'interval_months' => 1],
            'is_active' => true,
            'start_date' => now()->subMonth(),
            'last_executed_at' => now(),
        ]);

        $service = app(RecurringTransactionScheduleService::class);
        $transaction = $service->execute($recurring);

        $this->assertNull($transaction);
    }

    public function test_inactive_recurring_does_not_execute(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $today = now()->day;

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 100000,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => $today, 'interval_months' => 1],
            'is_active' => false,
            'start_date' => now()->subMonth(),
        ]);

        $service = app(RecurringTransactionScheduleService::class);
        $transaction = $service->execute($recurring);

        $this->assertNull($transaction);
    }

    public function test_recurring_outside_date_range_does_not_execute(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 100000,
            'frequency' => 'daily',
            'schedule_config' => ['interval_days' => 1],
            'is_active' => true,
            'start_date' => now()->addDay(),
        ]);

        $service = app(RecurringTransactionScheduleService::class);
        $transaction = $service->execute($recurring);

        $this->assertNull($transaction);
    }

    public function test_process_all_recurring_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $today = now()->day;

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 100000,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => $today, 'interval_months' => 1],
            'is_active' => true,
            'start_date' => now()->subMonth(),
            'last_executed_at' => null,
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 200000,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => $today, 'interval_months' => 1],
            'is_active' => true,
            'start_date' => now()->subMonth(),
            'last_executed_at' => null,
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 300000,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => $today + 1, 'interval_months' => 1],
            'is_active' => true,
            'start_date' => now()->subMonth(),
            'last_executed_at' => null,
        ]);

        $service = app(RecurringTransactionScheduleService::class);
        $count = $service->processAll();

        $this->assertEquals(2, $count);

        $wallet->refresh();
        $this->assertEquals(300000, $wallet->balance);
    }
}
