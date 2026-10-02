<?php

namespace App\Policies;

use App\Models\Mesa;
use App\Models\User;

class MesaPolicy
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
    public function view(User $user, Mesa $mesa): bool
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
    public function update(User $user, Mesa $mesa): bool
    {
        return $user->papel->podeGerenciarSalao();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mesa $mesa): bool
    {
        return $user->papel->podeGerenciarSalao() && ! $mesa->comandas()->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Mesa $mesa): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Mesa $mesa): bool
    {
        return false;
    }
}
