<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'staff', 'display_name' => 'Staff'],
            ['name' => 'admin_lokasi', 'display_name' => 'Admin Lokasi'],
            ['name' => 'admin_pusat', 'display_name' => 'Admin Pusat'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']],
            );
        }
    }
}
