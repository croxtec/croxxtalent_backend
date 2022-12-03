<?php

namespace App\Policies;

use App\Models\Timezone;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimezonePolicy
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
     * @param  \App\Models\Timezone  $language
     * @return mixed
     */
    public function view(?User $user, Timezone $language)
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
        if ($user->tokenCan('access:sysadmin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('create-timezone'); 
        }
        return false; 
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timezone  $language
     * @return mixed
     */
    public function update(User $user, Timezone $language)
    {
        if ($user->tokenCan('access:sysadmin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('update-timezone'); 
        }
        return false;
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timezone  $language
     * @return mixed
     */
    public function delete(User $user, Timezone $language)
    {
        if ($user->tokenCan('access:sysadmin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-timezone'); 
        }
        return false;
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timezone  $language
     * @return mixed
     */
    public function restore(User $user, Timezone $language)
    {
        if ($user->tokenCan('access:sysadmin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-timezone'); 
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timezone  $language
     * @return mixed
     */
    public function forceDelete(User $user, Timezone $language)
    {
        if ($user->tokenCan('access:sysadmin')) {
            return $user->role->is_owner 
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-timezone'); 
        }
        return false; 
    }
}
