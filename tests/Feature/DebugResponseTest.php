<?php

namespace Tests\Feature;

use App\Models\RecurringTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_response_content(): void
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

        $content = $response->getContent();

        file_put_contents('/tmp/response.html', $content);

        echo "\nResponse length: ".strlen($content)." bytes\n";
        echo 'User A count: '.substr_count($content, 'User A Recurring')."\n";
        echo 'User B count: '.substr_count($content, 'User B Recurring')."\n";

        $this->assertTrue(true);
    }
}
