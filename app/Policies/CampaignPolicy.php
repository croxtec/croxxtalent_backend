<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
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
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function view(?User $user, Campaign $campaign)
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
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->type === 'employer' || $user->type === 'admin'
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('create-campaign');
        }
        return false;
    }

    /**
     * Determine whether the user can update the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function update(User $user, Campaign $campaign)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $campaign->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('update-campaign'); 
        }
        return false;
    }

    /**
     * Determine whether the user can delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function delete(User $user, Campaign $campaign)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $campaign->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-campaign'); 
        }
        return false;
    }

    /**
     * Determine whether the user can restore the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function restore(User $user, Campaign $campaign)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $campaign->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-campaign'); 
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Campaign  $campaign
     * @return mixed
     */
    public function forceDelete(User $user, Campaign $campaign)
    {
        if ($user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
            return $user->id === $campaign->user_id
                    || $user->role->is_owner
                    || $user->role->is_admin
                    || $user->role->hasPermission('delete-campaign'); 
        }
        return false;
    }
}
