<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_dashboard(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100000]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'wallets',
                    'active_wallet',
                    'summary' => [
                        'total_income',
                        'total_expense',
                        'balance',
                        'month',
                        'year',
                    ],
                    'recent_transactions',
                    'category_data',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    public function test_dashboard_returns_user_wallets(): void
    {
        $user = User::factory()->create();
        $wallet1 = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Wallet A']);
        $wallet2 = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Wallet B']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.wallets'));
    }

    public function test_dashboard_returns_correct_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 500000]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(500000, (int) $response->json('data.summary.balance'));
    }

    public function test_dashboard_calculates_income_and_expense(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $category = Category::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'income',
            'amount' => 1000000,
            'transaction_date' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 300000,
            'transaction_date' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_income', 1000000)
            ->assertJsonPath('data.summary.total_expense', 300000);
    }

    public function test_dashboard_returns_category_data_for_expenses(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);
        $category = Category::factory()->create(['name' => 'Makanan', 'color' => '#FF0000']);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'transaction_date' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);

        $categoryData = $response->json('data.category_data');
        $this->assertNotEmpty($categoryData);
        $this->assertEquals('Makanan', $categoryData[0]['name']);
        $this->assertEquals(50000, $categoryData[0]['total']);
    }

    public function test_dashboard_filters_by_specific_wallet(): void
    {
        $user = User::factory()->create();
        $wallet1 = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Wallet 1', 'balance' => 0]);
        $wallet2 = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Wallet 2', 'balance' => 0]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet1->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet2->id,
            'type' => 'expense',
            'amount' => 200000,
            'transaction_date' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard?wallet_id='.$wallet1->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_expense', 100000);
    }

    public function test_dashboard_filters_by_month_and_year(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 100000,
            'transaction_date' => now()->subMonth(),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 200000,
            'transaction_date' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard?month='.now()->month.'&year='.now()->year);

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_expense', 200000);
    }

    public function test_dashboard_only_shows_recent_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);

        for ($i = 0; $i < 10; $i++) {
            Transaction::factory()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'expense',
                'amount' => 10000,
                'transaction_date' => now()->subDays($i),
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.recent_transactions'));
    }
}
