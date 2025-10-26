<?php

namespace App\Policies;

use App\Models\Professor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProfessorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-professores');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Professor $professor): bool
    {
        // Admin pode ver qualquer professor
        if ($user->can('view-professores')) {
            return true;
        }
        
        // Professor pode ver seus próprios dados
        return $user->id === $professor->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-professores');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Professor $professor): bool
    {
        // Admin pode editar qualquer professor
        if ($user->can('edit-professores')) {
            return true;
        }
        
        // Professor pode editar seus próprios dados
        return $user->id === $professor->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Professor $professor): bool
    {
        return $user->can('delete-professores');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Professor $professor): bool
    {
        return $user->can('delete-professores');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Professor $professor): bool
    {
        return $user->can('delete-professores');
    }
}