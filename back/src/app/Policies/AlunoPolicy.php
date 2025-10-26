<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AlunoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-alunos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $aluno): bool
    {
        // Admin e Professor podem ver qualquer aluno
        if ($user->can('view-alunos')) {
            return true;
        }
        
        // Aluno pode ver apenas seus próprios dados
        return $user->id === $aluno->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-alunos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $aluno): bool
    {
        // Admin e Professor podem editar qualquer aluno
        if ($user->can('edit-alunos')) {
            return true;
        }
        
        // Aluno pode editar apenas seus próprios dados
        return $user->id === $aluno->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $aluno): bool
    {
        return $user->can('delete-alunos');
    }
}
