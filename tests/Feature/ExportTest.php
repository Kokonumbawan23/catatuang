<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_csv_export_returns_200_status(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('transactions.export', [
            'format' => 'csv',
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_csv_export_contains_transaction_data(): void
    {
        $this->actingAs($this->user);

        $category = Category::factory()->expense()->create(['name' => 'Makanan']);
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Test expense',
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('transactions.export', [
            'format' => 'csv',
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Makanan', $content);
        $this->assertStringContainsString('50.000', $content);
    }

    public function test_unauthenticated_user_cannot_export(): void
    {
        $response = $this->get(route('transactions.export', [
            'format' => 'csv',
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertRedirect('/login');
    }

    public function test_csv_export_filters_by_type(): void
    {
        $this->actingAs($this->user);

        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();

        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('transactions.export', [
            'format' => 'csv',
            'month' => now()->month,
            'year' => now()->year,
            'type' => 'income',
        ]));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Pemasukan', $content);
    }

    public function test_invalid_export_format_returns_404(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('transactions.export', [
            'format' => 'invalid',
            'month' => now()->month,
            'year' => now()->year,
        ]));

        $response->assertStatus(404);
    }
}
