<?php

namespace App\Policies;

use App\Models\Degree;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DegreePolicy
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
        // any one can perform this action
        return true; 
    }

    /**
     * Determine whether the user can view the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Degree  $degree
     * @return mixed
     */
    public function view(?User $user, Degree $degree)
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
        if ($user->tokenCan('access:admin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('create-degree'); 
        }
        return false; 
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Degree  $degree
     * @return mixed
     */
    public function update(User $user, Degree $degree)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('update-degree'); 
        }
        return false;
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Degree  $degree
     * @return mixed
     */
    public function delete(User $user, Degree $degree)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-degree'); 
        }
        return false;
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Degree  $degree
     * @return mixed
     */
    public function restore(User $user, Degree $degree)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-degree'); 
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Degree  $degree
     * @return mixed
     */
    public function forceDelete(User $user, Degree $degree)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-degree'); 
        }
        return false;
    }
}
