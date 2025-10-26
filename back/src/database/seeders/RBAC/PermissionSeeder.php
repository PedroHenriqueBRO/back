<?php

namespace Database\Seeders\RBAC;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // 1. CRIAR TODAS AS PERMISSÕES
        // ========================================
        
        // Permissões de Atividades
        Permission::firstOrCreate(['name' => 'view-atividades']);
        Permission::firstOrCreate(['name' => 'create-atividades']);
        Permission::firstOrCreate(['name' => 'edit-atividades']);
        Permission::firstOrCreate(['name' => 'delete-atividades']);

        // Permissões de Problemas
        Permission::firstOrCreate(['name' => 'view-problemas']);
        Permission::firstOrCreate(['name' => 'create-problemas']);
        Permission::firstOrCreate(['name' => 'edit-problemas']);
        Permission::firstOrCreate(['name' => 'delete-problemas']);

        // Permissões de Alunos
        Permission::firstOrCreate(['name' => 'view-alunos']);
        Permission::firstOrCreate(['name' => 'create-alunos']);
        Permission::firstOrCreate(['name' => 'edit-alunos']);
        Permission::firstOrCreate(['name' => 'delete-alunos']);

        // Permissões de Professores
        Permission::firstOrCreate(['name' => 'view-professores']);
        Permission::firstOrCreate(['name' => 'create-professores']);
        Permission::firstOrCreate(['name' => 'edit-professores']);
        Permission::firstOrCreate(['name' => 'delete-professores']);

        // Permissões de Submissões
        Permission::firstOrCreate(['name' => 'view-submissoes']);
        Permission::firstOrCreate(['name' => 'create-submissoes']);
        Permission::firstOrCreate(['name' => 'view-own-submissoes']); // Aluno vê apenas as suas

        // Permissões de Correções
        Permission::firstOrCreate(['name' => 'view-correcoes']);
        Permission::firstOrCreate(['name' => 'create-correcoes']);

        // ========================================
        // 2. ATRIBUIR PERMISSÕES ÀS ROLES
        // ========================================

        // Role: Admin (acesso total)
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        // Role: Professor (gerencia atividades, problemas, vê alunos, corrige submissões)
        $professorRole = Role::findByName('professor');
        $professorRole->givePermissionTo([
            'view-atividades',
            'create-atividades',
            'edit-atividades',
            'delete-atividades',
            
            'view-problemas',
            'create-problemas',
            'edit-problemas',
            'delete-problemas',
            
            'view-alunos',
            
            'view-submissoes',
            
            'view-correcoes',
            'create-correcoes',
        ]);

        // Role: Student (vê atividades/problemas, cria submissões, vê próprias submissões)
        $studentRole = Role::findByName('student');
        $studentRole->givePermissionTo([
            'view-atividades',
            'view-problemas',
            'create-submissoes',
            'view-own-submissoes',
        ]);
    }
}
