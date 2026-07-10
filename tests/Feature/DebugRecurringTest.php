<?php

namespace Tests\Feature;

use App\Models\RecurringTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugRecurringTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_user_isolation(): void
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

        // Find all occurrences of User A and User B
        preg_match_all('/User [AB] Recurring/', $content, $matches);
        echo 'Found: '.implode(', ', $matches[0])."\n";

        // Check if User B is in the recurring transactions section
        if (preg_match('/User B Recurring.*?<div.*?class="grid.*?recurrings/s', $content)) {
            echo "User B Found in recurring grid\n";
        }

        // Check where User B appears
        $pos = strpos($content, 'User B Recurring');
        if ($pos !== false) {
            echo "First occurrence at position: $pos\n";
            echo 'Surrounding context: '.substr($content, max(0, $pos - 200), 400)."\n";
        }
    }
}
