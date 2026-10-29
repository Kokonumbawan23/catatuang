<?php

namespace Tests\Feature\Api;

use App\Enums\RecurringOccurrenceStatus;
use App\Jobs\SendRecurringConfirmationReminder;
use App\Models\Category;
use App\Models\RecurringOccurrence;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RecurringTransactionScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecurringOccurrenceApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeConfirmableRecurring(User $user, float $walletBalance = 1000000): RecurringTransaction
    {
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => $walletBalance]);

        return RecurringTransaction::factory()->daily()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => Category::factory()->create()->id,
            'type' => 'expense',
            'amount' => 300000,
            'requires_confirmation' => true,
        ]);
    }

    public function test_scheduler_creates_pending_occurrence_instead_of_transaction(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $recurring = $this->makeConfirmableRecurring($user);

        app(RecurringTransactionScheduleService::class)->processAll();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertEquals(1000000, $recurring->wallet->fresh()->balance);
        $this->assertDatabaseHas('recurring_occurrences', [
            'recurring_transaction_id' => $recurring->id,
            'status' => RecurringOccurrenceStatus::Pending->value,
        ]);
        $this->assertNotNull($recurring->fresh()->last_executed_at);
        Queue::assertPushed(SendRecurringConfirmationReminder::class);
    }

    public function test_scheduler_does_not_create_duplicate_occurrence_on_same_day(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $this->makeConfirmableRecurring($user);
        $service = app(RecurringTransactionScheduleService::class);

        $service->processAll();
        $service->processAll();

        $this->assertDatabaseCount('recurring_occurrences', 1);
    }

    public function test_new_recurring_requires_confirmation_by_default(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/recurring-transactions', [
            'wallet_id' => $wallet->id,
            'title' => 'Bayar WiFi',
            'amount' => 350000,
            'type' => 'expense',
            'category_id' => Category::factory()->create()->id,
            'frequency' => 'monthly',
            'schedule_config' => ['day_of_month' => 10, 'interval_months' => 1],
            'start_date' => now()->toDateString(),
        ])->assertStatus(201);

        $this->assertTrue(RecurringTransaction::first()->fresh()->requires_confirmation);
    }

    public function test_user_can_confirm_occurrence_and_transaction_is_recorded(): void
    {
        $user = User::factory()->create();
        $recurring = $this->makeConfirmableRecurring($user);
        $occurrence = RecurringOccurrence::factory()->create([
            'recurring_transaction_id' => $recurring->id,
            'due_date' => now()->subDays(2)->toDateString(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/confirm")->assertStatus(200);

        $transaction = Transaction::first();
        $this->assertEquals(300000, $transaction->amount);
        $this->assertEquals($occurrence->due_date->toDateString(), $transaction->transaction_date->toDateString());
        $this->assertEquals(700000, $recurring->wallet->fresh()->balance);
        $this->assertEquals(RecurringOccurrenceStatus::Confirmed, $occurrence->fresh()->status);
        $this->assertEquals($transaction->id, $occurrence->fresh()->transaction_id);
    }

    public function test_user_can_confirm_occurrence_with_actual_amount(): void
    {
        $user = User::factory()->create();
        $recurring = $this->makeConfirmableRecurring($user);
        $occurrence = RecurringOccurrence::factory()->create(['recurring_transaction_id' => $recurring->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/confirm", ['amount' => 347500])
            ->assertStatus(200);

        $this->assertEquals(347500, Transaction::first()->amount);
        $this->assertEquals(652500, $recurring->wallet->fresh()->balance);
    }

    public function test_confirming_twice_does_not_record_duplicate_transaction(): void
    {
        $user = User::factory()->create();
        $recurring = $this->makeConfirmableRecurring($user);
        $occurrence = RecurringOccurrence::factory()->create(['recurring_transaction_id' => $recurring->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/confirm")->assertStatus(200);
        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/confirm")->assertStatus(422);

        $this->assertDatabaseCount('transactions', 1);
        $this->assertEquals(700000, $recurring->wallet->fresh()->balance);
    }

    public function test_user_can_skip_occurrence_without_recording_transaction(): void
    {
        $user = User::factory()->create();
        $recurring = $this->makeConfirmableRecurring($user);
        $occurrence = RecurringOccurrence::factory()->create(['recurring_transaction_id' => $recurring->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/skip")->assertStatus(200);
        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/confirm")->assertStatus(422);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertEquals(RecurringOccurrenceStatus::Skipped, $occurrence->fresh()->status);
        $this->assertEquals(1000000, $recurring->wallet->fresh()->balance);
    }

    public function test_user_cannot_confirm_or_skip_other_users_occurrence(): void
    {
        $owner = User::factory()->create();
        $occurrence = RecurringOccurrence::factory()->create([
            'recurring_transaction_id' => $this->makeConfirmableRecurring($owner)->id,
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/confirm")->assertStatus(403);
        $this->postJson("/api/recurring-occurrences/{$occurrence->id}/skip")->assertStatus(403);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertEquals(RecurringOccurrenceStatus::Pending, $occurrence->fresh()->status);
    }

    public function test_pending_occurrences_are_listed_only_for_owner(): void
    {
        $user = User::factory()->create();
        RecurringOccurrence::factory()->create([
            'recurring_transaction_id' => $this->makeConfirmableRecurring($user)->id,
        ]);
        RecurringOccurrence::factory()->create([
            'recurring_transaction_id' => $this->makeConfirmableRecurring(User::factory()->create())->id,
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/recurring-transactions')
            ->assertStatus(200)
            ->assertJsonCount(1, 'meta.pending_occurrences');

        $this->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.pending_confirmations_count', 1);
    }
}
