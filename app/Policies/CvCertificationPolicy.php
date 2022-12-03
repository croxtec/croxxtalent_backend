<?php

namespace App\Policies;

use App\Models\Cv;
use App\Models\CvCertification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CvCertificationPolicy
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
     * @param  \App\Models\Cv  $cv
     * @return mixed
     */
    public function view(?User $user, Cv $cv)
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
        if ($user->tokenCan('access:talent')) {
            return $user->type === 'talent'
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('create-cv');
        }
        return false;
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cv  $cv
     * @return mixed
     */
    public function update(User $user, Cv $cv)
    {
        if ($user->tokenCan('access:talent') || $user->tokenCan('access:admin')) {
            return $user->id === $cv->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('update-cv'); 
        }
        return false;
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cv  $cv
     * @return mixed
     */
    public function delete(User $user, Cv $cv)
    {
        if ($user->tokenCan('access:talent') || $user->tokenCan('access:admin')) {
            return $user->id === $cv->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-cv'); 
        }
        return false;
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cv  $cv
     * @return mixed
     */
    public function restore(User $user, Cv $cv)
    {
        if ($user->tokenCan('access:talent') || $user->tokenCan('access:admin')) {
            return $user->id === $cv->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-cv'); 
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cv  $cv
     * @return mixed
     */
    public function forceDelete(User $user, Cv $cv)
    {
        if ($user->tokenCan('access:talent') || $user->tokenCan('access:admin')) {
            return $user->id === $cv->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-cv'); 
        }
        return false;
    }
}
