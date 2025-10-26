# Como Verificar Permissões de Usuários Criados pelos Seeders

## Método 1: Via Artisan Tinker (Terminal)

### Passo 1: Acesse o Tinker
```bash
php artisan tinker
```

### Passo 2: Verificar Usuário Específico

#### Por Email:
```php
$user = \App\Models\User::where('email', 'ana.carolina@email.com')->first();
```

#### Por ID:
```php
$user = \App\Models\User::find(1);
```

### Passo 3: Verificar Roles (Papéis)

```php
// Ver todas as roles do usuário
$user->roles;

// Ver apenas os nomes das roles
$user->roles->pluck('name');

// Verificar se tem uma role específica
$user->hasRole('student');
$user->hasRole('professor');
$user->hasRole('admin');
```

### Passo 4: Verificar Permissions (Permissões)

```php
// Ver todas as permissões (diretas + via roles)
$user->getAllPermissions();

// Ver apenas os nomes das permissões
$user->getAllPermissions()->pluck('name');

// Verificar se tem uma permissão específica
$user->can('view-atividades');
$user->can('create-submissoes');
$user->can('edit-alunos');

// Ver permissões diretas (sem as das roles)
$user->permissions;

// Ver permissões via roles
$user->getPermissionsViaRoles();
```

### Exemplo Completo no Tinker:

```php
// 1. Buscar um aluno
$aluno = \App\Models\User::where('email', 'ana.carolina@email.com')->first();

// 2. Ver informações básicas
$aluno->name;              // "Ana Carolina"
$aluno->email;             // "ana.carolina@email.com"

// 3. Ver roles
$aluno->roles->pluck('name');  // ["student"]

// 4. Ver todas as permissões
$aluno->getAllPermissions()->pluck('name');
// Resultado esperado:
// ["view-atividades", "view-problemas", "create-submissoes", "view-own-submissoes"]

// 5. Testar permissões específicas
$aluno->can('view-atividades');        // true
$aluno->can('create-atividades');      // false (só professor/admin)
$aluno->can('create-submissoes');      // true
```

---

## Método 2: Criar uma Rota de Debug (API)

### Criar endpoint temporário em `routes/api.php`:

```php
// ATENÇÃO: Remover em produção!
Route::middleware('auth:sanctum')->get('/debug/user-permissions', function () {
    $user = auth()->user();
    
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'roles' => $user->roles->pluck('name'),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'has_student_role' => $user->hasRole('student'),
        'has_professor_role' => $user->hasRole('professor'),
        'has_admin_role' => $user->hasRole('admin'),
        'can_view_atividades' => $user->can('view-atividades'),
        'can_create_atividades' => $user->can('create-atividades'),
        'can_create_submissoes' => $user->can('create-submissoes'),
    ]);
});
```

### Testar no Postman/Insomnia:

```
GET http://localhost:8000/api/debug/user-permissions
Headers:
  Authorization: Bearer {seu_token}
  Accept: application/json
```

---

## Método 3: Comando Artisan Personalizado

### Criar comando:
```bash
php artisan make:command CheckUserPermissions
```

### Implementar o comando (`app/Console/Commands/CheckUserPermissions.php`):

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserPermissions extends Command
{
    protected $signature = 'user:check-permissions {email}';
    protected $description = 'Verifica roles e permissões de um usuário';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário com email {$email} não encontrado!");
            return 1;
        }

        $this->info("=== Informações do Usuário ===");
        $this->line("ID: {$user->id}");
        $this->line("Nome: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->newLine();

        $this->info("=== Roles (Papéis) ===");
        foreach ($user->roles as $role) {
            $this->line("- {$role->name}");
        }
        $this->newLine();

        $this->info("=== Permissões ===");
        foreach ($user->getAllPermissions() as $permission) {
            $this->line("- {$permission->name}");
        }

        return 0;
    }
}
```

### Usar o comando:
```bash
php artisan user:check-permissions ana.carolina@email.com
```

---

## Método 4: Consulta SQL Direta

### No terminal do banco de dados:

```sql
-- Ver roles do usuário
SELECT u.id, u.name, u.email, r.name as role
FROM users u
LEFT JOIN model_has_roles mr ON u.id = mr.model_id
LEFT JOIN roles r ON mr.role_id = r.id
WHERE u.email = 'ana.carolina@email.com';

-- Ver todas as permissões (diretas + via roles)
SELECT u.id, u.name, p.name as permission, 
       CASE WHEN mp.model_id IS NOT NULL THEN 'direta' ELSE 'via role' END as tipo
FROM users u
LEFT JOIN model_has_roles mr ON u.id = mr.model_id
LEFT JOIN roles r ON mr.role_id = r.id
LEFT JOIN role_has_permissions rp ON r.id = rp.role_id
LEFT JOIN permissions p ON rp.permission_id = p.id OR p.id = mp.permission_id
LEFT JOIN model_has_permissions mp ON u.id = mp.model_id AND p.id = mp.permission_id
WHERE u.email = 'ana.carolina@email.com';
```

---

## Método 5: Adicionar no Seeder para Auto-verificação

### Adicionar ao final do `AlunoSeeder.php`:

```php
public function run(): void
{
    // ...existing seeding code...

    // Verificação automática
    $this->command->info("=== Verificando permissões dos alunos criados ===");
    
    $alunos = User::whereHas('aluno')->get();
    
    foreach ($alunos as $aluno) {
        $this->command->line("Aluno: {$aluno->name}");
        $this->command->line("  - Roles: " . $aluno->roles->pluck('name')->implode(', '));
        $this->command->line("  - Permissões: " . $aluno->getAllPermissions()->pluck('name')->implode(', '));
        $this->command->newLine();
    }
}
```

---

## Método 6: Teste Unitário

### Criar teste:
```bash
php artisan make:test UserPermissionsTest
```

### Implementar (`tests/Feature/UserPermissionsTest.php`):

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RBAC\RoleSeeder;
use Database\Seeders\RBAC\PermissionSeeder;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Roda os seeders necessários
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    public function test_student_has_correct_permissions()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        // Verificar role
        $this->assertTrue($student->hasRole('student'));

        // Verificar permissões corretas
        $this->assertTrue($student->can('view-atividades'));
        $this->assertTrue($student->can('view-problemas'));
        $this->assertTrue($student->can('create-submissoes'));
        $this->assertTrue($student->can('view-own-submissoes'));

        // Verificar permissões negadas
        $this->assertFalse($student->can('create-atividades'));
        $this->assertFalse($student->can('edit-atividades'));
        $this->assertFalse($student->can('delete-atividades'));
    }

    public function test_professor_has_correct_permissions()
    {
        $professor = User::factory()->create();
        $professor->assignRole('professor');

        // Verificar role
        $this->assertTrue($professor->hasRole('professor'));

        // Verificar permissões corretas
        $this->assertTrue($professor->can('view-atividades'));
        $this->assertTrue($professor->can('create-atividades'));
        $this->assertTrue($professor->can('edit-atividades'));
        $this->assertTrue($professor->can('view-alunos'));
        $this->assertTrue($professor->can('view-submissoes'));
        $this->assertTrue($professor->can('create-correcoes'));
    }
}
```

### Executar teste:
```bash
php artisan test --filter UserPermissionsTest
```

---

## Resumo dos Comandos Mais Úteis

### Verificação Rápida (Tinker):
```bash
php artisan tinker

# Depois no tinker:
$user = User::where('email', 'ana.carolina@email.com')->first();
$user->roles->pluck('name');
$user->getAllPermissions()->pluck('name');
```

### Limpar e Recriar Seeds:
```bash
php artisan migrate:fresh --seed
```

### Ver todas as roles e permissões do sistema:
```bash
php artisan tinker

# No tinker:
\Spatie\Permission\Models\Role::with('permissions')->get();
```

---

## Checklist de Verificação

- [ ] Usuário foi criado?
- [ ] Usuário tem a role correta atribuída?
- [ ] A role tem as permissões esperadas?
- [ ] `$user->can('permissao-especifica')` retorna `true`?
- [ ] As tabelas `model_has_roles` e `role_has_permissions` estão populadas?

---

## Troubleshooting

### Problema: Permissões não aparecem
**Solução:**
```bash
# Limpar cache de permissões
php artisan permission:cache-reset

# Recriar seeds
php artisan migrate:fresh --seed
```

### Problema: Role não foi atribuída
**Verificar no seeder se tem:**
```php
$user->assignRole('student'); // ou 'professor' ou 'admin'
```
