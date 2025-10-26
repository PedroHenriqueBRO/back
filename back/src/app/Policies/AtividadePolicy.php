<?php

namespace App\Policies;

use App\Models\Atividade;
use App\Models\User;

class AtividadePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-atividades');
    }

    public function view(User $user, Atividade $atividade): bool
    {
        return $user->can('view-atividades');
    }

    public function create(User $user): bool
    {
        return $user->can('create-atividades');
    }

    public function update(User $user, Atividade $atividade): bool
    {
        return $user->can('edit-atividades');
    }

    public function delete(User $user, Atividade $atividade): bool
    {
        return $user->can('delete-atividades');
    }
}
