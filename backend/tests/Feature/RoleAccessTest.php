<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_locations_endpoint(): void
    {
        $staffRole = Role::query()->create([
            'name' => 'staff',
            'display_name' => 'Staff',
        ]);

        $user = User::query()->create([
            'name' => 'Staff User',
            'email' => 'staff@mbg.local',
            'password' => 'password123',
            'role_id' => $staffRole->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/locations')
            ->assertForbidden();
    }
}
