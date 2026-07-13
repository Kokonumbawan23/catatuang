# 10 - Security Tests

## Gambaran Umum

Automated tests yang memverifikasi keamanan aplikasi, khususnya:
1. Isolasi data wallet antar-user
2. Validasi nominal transaksi (tidak boleh 0 atau negatif)

## Test File

```
tests/Feature/WalletSecurityTest.php
```

## Test Cases

### Wallet Isolation Tests

Memastikan user tidak bisa mengakses wallet user lain:

```php
public function test_user_cannot_see_other_users_wallets(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $wallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson('/api/wallets');

    $response->assertStatus(200);
    $this->assertCount(0, $response->json('data'));
}

public function test_user_cannot_update_other_users_wallet(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $wallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/api/wallets/{$wallet->id}", [
        'name' => 'Hacked Wallet',
    ]);

    $response->assertStatus(403);
}

public function test_user_cannot_delete_other_users_wallet(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $wallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/api/wallets/{$wallet->id}");

    $response->assertStatus(403);
}
```

### Amount Validation Tests

Memastikan transaksi bernilai positif:

```php
public function test_transaction_amount_must_be_positive(): void
{
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    // Amount = 0 should fail
    $response = $this->actingAs($user)->postJson('/api/transactions', [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'out',
        'amount' => 0,
        'transaction_date' => now()->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['amount']);
}

public function test_transaction_amount_cannot_be_negative(): void
{
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/transactions', [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'out',
        'amount' => -50000,
        'transaction_date' => now()->format('Y-m-d'),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['amount']);
}

public function test_guest_cannot_access_wallet(): void
{
    $wallet = Wallet::factory()->create();

    $response = $this->getJson('/api/wallets');

    $response->assertStatus(401);
}
```

## Running Tests

```bash
# Run all security tests
php artisan test tests/Feature/WalletSecurityTest.php

# Run single test
php artisan test --filter=test_user_cannot_see_other_users_wallets

# Run all tests
php artisan test
```

## Security Coverage

| Attack Vector | Protection |
|--------------|------------|
| Data leakage between users | WalletPolicy, TransactionPolicy |
| Invalid transaction amounts | Validation rule `gt:0` |
| Unauthorized API access | auth:sanctum middleware |
| Cross-user wallet modification | Policy authorization checks |

## Related Files

- `tests/Feature/WalletSecurityTest.php`
- `app/Policies/WalletPolicy.php`
- `app/Policies/TransactionPolicy.php`
- `app/Http/Requests/StoreTransactionRequest.php`
