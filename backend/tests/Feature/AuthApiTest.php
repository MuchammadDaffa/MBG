<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $role = Role::query()->create([
            'name' => 'admin_pusat',
            'display_name' => 'Admin Pusat',
        ]);

        User::query()->create([
            'name' => 'Admin Pusat',
            'email' => 'admin@mbg.local',
            'password' => 'password123',
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@mbg.local',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);
    }

    public function test_v1_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/items')
            ->assertUnauthorized();
    }
}
