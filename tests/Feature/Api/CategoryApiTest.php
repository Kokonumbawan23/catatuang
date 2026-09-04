<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_categories(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'type', 'icon', 'color'],
                ],
            ]);
    }

    public function test_type_filter_returns_only_matching_categories_as_a_json_array(): void
    {
        $user = User::factory()->create();
        // Sengaja diselang-seling: kalau reindex-nya salah, kategori expense
        // yang tersisa akan punya key non-sekuensial (0 dan 2) setelah difilter.
        Category::factory()->create(['type' => 'expense']);
        Category::factory()->create(['type' => 'income']);
        Category::factory()->create(['type' => 'expense']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories?type=expense');

        $response->assertStatus(200)->assertJsonCount(2, 'data');

        $data = $response->json('data');
        $this->assertTrue(array_is_list($data), 'Response data harus tetap berupa JSON array, bukan object.');
        $this->assertSame(['expense', 'expense'], array_column($data, 'type'));
    }

    public function test_unauthenticated_user_cannot_list_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401);
    }
}
