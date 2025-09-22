<?php

namespace App\Policies;

use App\Models\Requisicoes;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RequisicoesPolicy
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
    public function view(User $user, Requisicoes $requisicoes): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->cargo === 'administrador' || $user->cargo === 'relator'){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Requisicoes $requisicoes): bool
    {
        if($user->cargo === 'administrador' || $user->cargo === 'atendente' || $user->cargo === 'gerente' || $user->cargo === 'relator'){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Requisicoes $requisicoes): bool
    {
        if($user->cargo === 'administrador' || $user->cargo === 'relator'){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Requisicoes $requisicoes): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Requisicoes $requisicoes): bool
    {
        return false;
    }
}
