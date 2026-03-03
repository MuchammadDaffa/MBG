<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminPusatSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('name', 'admin_pusat')->first();

        if (! $role) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@mbg.local'],
            [
                'name' => 'Admin Pusat',
                'password' => 'password123',
                'role_id' => $role->id,
            ]
        );
    }
}
