<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_wallets(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Wallet A']);
        Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Wallet B']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'balance', 'transactions_count'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_list_wallets(): void
    {
        $response = $this->getJson('/api/wallets');

        $response->assertStatus(401);
    }

    public function test_user_can_create_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallets', [
            'name' => 'Dompet Baru',
            'balance' => 500000,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Dompet berhasil dibuat.',
                'data' => [
                    'name' => 'Dompet Baru',
                    'balance' => 500000,
                ],
            ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'name' => 'Dompet Baru',
        ]);
    }

    public function test_user_can_view_own_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'My Wallet']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallets/'.$wallet->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $wallet->id,
                    'name' => 'My Wallet',
                ],
            ]);
    }

    public function test_user_cannot_view_other_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        Sanctum::actingAs($userA);

        $response = $this->getJson('/api/wallets/'.$walletB->id);

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'name' => 'Old Name', 'balance' => 100000]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/wallets/'.$wallet->id, [
            'name' => 'New Name',
            'balance' => 100000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Dompet berhasil diperbarui.',
                'data' => [
                    'name' => 'New Name',
                ],
            ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_cannot_update_other_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id, 'balance' => 100000]);
        Sanctum::actingAs($userA);

        $response = $this->putJson('/api/wallets/'.$walletB->id, [
            'name' => 'Hacked Name',
            'balance' => 999999,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/wallets/'.$wallet->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Dompet berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('wallets', [
            'id' => $wallet->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_wallet(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $walletB = Wallet::factory()->create(['user_id' => $userB->id]);
        Sanctum::actingAs($userA);

        $response = $this->deleteJson('/api/wallets/'.$walletB->id);

        $response->assertStatus(403);

        $this->assertDatabaseHas('wallets', [
            'id' => $walletB->id,
        ]);
    }

    public function test_wallet_creation_requires_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallets', [
            'balance' => 100000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_wallet_list_only_shows_users_wallets(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Wallet::factory()->create(['user_id' => $userA->id, 'name' => 'User A Wallet']);
        Wallet::factory()->create(['user_id' => $userB->id, 'name' => 'User B Wallet']);
        Sanctum::actingAs($userA);

        $response = $this->getJson('/api/wallets');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('User A Wallet', $response->json('data.0.name'));
    }
}
