<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RBAC\RoleSeeder;
use Database\Seeders\RBAC\PermissionSeeder;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          // 1. Primeiro cria as roles
            PermissionSeeder::class,    // 2. Depois atribui permissões às roles
            UserSeeder::class,          // 3. Cria o usuário admin
            CursoSeeder::class,         // 4. Cria os cursos
            AlunoSeeder::class,
        ]);
    }
}
