<?php

namespace Database\Seeders\RBAC;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //Admin Role
        Role::firstOrCreate(['name' => 'admin']);

        //Professor Role
        Role::firstOrCreate(['name' => 'professor']);

        //Student Role
        Role::firstOrCreate(['name' => 'student']);
    }
}
