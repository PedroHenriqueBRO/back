<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa as permissões dos usuários';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TESTANDO PERMISSÕES ===');
        $this->newLine();

        // Testar Admin
        $admin = User::where('email', 'admin@admin.com')->first();
        if ($admin) {
            $this->info('ADMIN (admin@admin.com):');
            $this->line('  Roles: ' . $admin->getRoleNames()->implode(', '));
            $this->line('  Tem role admin? ' . ($admin->hasRole('admin') ? 'SIM' : 'NÃO'));
            $this->line('  Permissões: ' . $admin->getAllPermissions()->pluck('name')->implode(', '));
            $this->line('  Pode view-alunos? ' . ($admin->can('view-alunos') ? 'SIM' : 'NÃO'));
            $this->newLine();
        } else {
            $this->error('Admin não encontrado!');
        }

        // Testar Aluno
        $aluno = User::where('email', 'ana.carolina@email.com')->first();
        if ($aluno) {
            $this->info('ALUNO (ana.carolina@email.com):');
            $this->line('  Roles: ' . $aluno->getRoleNames()->implode(', '));
            $this->line('  Tem role student? ' . ($aluno->hasRole('student') ? 'SIM' : 'NÃO'));
            $this->line('  Permissões: ' . $aluno->getAllPermissions()->pluck('name')->implode(', '));
            $this->line('  Pode view-alunos? ' . ($aluno->can('view-alunos') ? 'SIM' : 'NÃO'));
            $this->line('  Pode view-atividades? ' . ($aluno->can('view-atividades') ? 'SIM' : 'NÃO'));
            $this->newLine();
        } else {
            $this->error('Aluno não encontrado!');
        }

        // Ver todas as roles e permissões
        $this->info('=== ROLES E PERMISSÕES NO SISTEMA ===');
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
        foreach ($roles as $role) {
            $this->line("Role: {$role->name}");
            $this->line("  Permissões: " . $role->permissions->pluck('name')->implode(', '));
            $this->newLine();
        }

        return 0;
    }
}
