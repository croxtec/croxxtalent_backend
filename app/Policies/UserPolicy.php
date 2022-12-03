<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any records.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(?User $user)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:affiliate') || $user->tokenCan('access:admin')) {
            return ($user && ($user->type == 'affiliate' || $user->type == 'admin'))
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('view-all-user'); 
        }
        return false;
    }

    /**
     * Determine whether the user can view the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $userData
     * @return mixed
     */
    public function view(?User $user, User $userData)
    {
        // any one can perform this action
        return true; 
    }

    /**
     * Determine whether the user can create record.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // if ($user->tokenCan('access:sysadmin')) {
        //     return $user->role->is_owner 
        //             || $user->role->is_admin
        //             || $user->role->hasPermission('create-user'); 
        // }
        return false; 
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $userData
     * @return mixed
     */
    public function update(User $user, User $userData)
    {
        if ($user->tokenCan('access:talent') || $user->tokenCan('access:employer') || $user->tokenCan('access:affiliate') || $user->tokenCan('access:admin')) {
            return $user->id === $userData->id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('update-user'); 
        }
        return false;
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $userData
     * @return mixed
     */
    public function delete(User $user, User $userData)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->id !== $userData->id
                    && (
                    $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-user')); 
        }
        return false;
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $userData
     * @return mixed
     */
    public function restore(User $user, User $userData)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->id !== $userData->id
                    && (
                    $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-user')); 
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $userData
     * @return mixed
     */
    public function forceDelete(User $user, User $userData)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->id !== $userData->id
                    && (
                    $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-user')); 
        }
        return false;
    }
}
