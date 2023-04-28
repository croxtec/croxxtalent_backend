<?php

namespace App\Policies;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfessionalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // info($user->tokenCan('access:admin'));
        if ($user->tokenCan('access:admin')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Professional  $professional
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Professional $professional)
    {
        if ($user->tokenCan('access:admin')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->tokenCan('access:admin')) {
            return $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('create-professional');
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Professional  $professional
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Professional $professional)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $professional->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('update-professional');
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Professional  $professional
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Professional $professional)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $professional->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-professional');
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Professional  $professional
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Professional $professional)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $professional->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-professional');
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Professional  $professional
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Professional $professional)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $professional->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-professional');
        }
        return false;
    }
}
