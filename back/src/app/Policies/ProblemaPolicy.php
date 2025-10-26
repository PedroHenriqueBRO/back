<?php

namespace App\Policies;

use App\Models\Problema;
use App\Models\User;

class ProblemaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-problemas');
    }

    public function view(User $user, Problema $problema): bool
    {
        return $user->can('view-problemas');
    }

    public function create(User $user): bool
    {
        return $user->can('create-problemas');
    }

    public function update(User $user, Problema $problema): bool
    {
        return $user->can('edit-problemas');
    }

    public function delete(User $user, Problema $problema): bool
    {
        return $user->can('delete-problemas');
    }
}
