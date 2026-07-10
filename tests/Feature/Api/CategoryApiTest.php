<?php

namespace Tests\Feature\Api;

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

    public function test_unauthenticated_user_cannot_list_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401);
    }
}
