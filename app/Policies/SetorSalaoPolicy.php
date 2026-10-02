<?php

namespace App\Policies;

use App\Models\SetorSalao;
use App\Models\User;

class SetorSalaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SetorSalao $setorSalao): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->papel->podeGerenciarSalao();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SetorSalao $setorSalao): bool
    {
        return $user->papel->podeGerenciarSalao();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SetorSalao $setorSalao): bool
    {
        return $user->papel->podeGerenciarSalao() && ! $setorSalao->mesas()->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SetorSalao $setorSalao): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SetorSalao $setorSalao): bool
    {
        return false;
    }
}
