<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_motors(): void
    {
        $response = $this->getJson('/api/motors');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_motor(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/motors', [
                'name' => 'Honda Vario 160',
                'brand' => 'Honda',
                'plate_number' => 'AD 1234 XYZ',
                'price_per_day' => 120000,
                'status' => 'available',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Honda Vario 160');

        $this->assertDatabaseHas('motors', [
            'plate_number' => 'AD 1234 XYZ',
        ]);
    }
}
