<?php

    namespace App\Policies;

    use App\Models\JobInvitation;
    use App\Models\User;
    use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class JobInvitationPolicy
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
         * @param  \App\Models\JobInvitation  $jobInvitation
         * @return mixed
         */
        public function view(?User $user, JobInvitation $jobInvitation)
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
                        || $user?->role->is_owner
                        || $user?->role->is_admin
                        || $user?->role->hasPermission('create-job-invitation');
            }
            return false;
        }

        /**
         * Determine whether the user can update the record.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\JobInvitation  $jobInvitation
         * @return mixed
         */
        public function update(User $user, JobInvitation $jobInvitation)
        {
            if ($user->tokenCan('access:talent') || $user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
                // info([ $user->id,$user?->role, $jobInvitation->talent_user_id]);
                return $user->id == $jobInvitation->talent_user_id || $user->id == $jobInvitation->employer_user_id
                        || $user?->role->is_owner
                        || $user?->role->is_admin
                        || $user?->role->hasPermission('update-job-invitation');
            }
            return false;
        }

        /**
         * Determine whether the user can delete the record.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\JobInvitation  $jobInvitation
         * @return mixed
         */
        public function delete(User $user, JobInvitation $jobInvitation)
        {
            if ($user->tokenCan('access:talent') || $user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
                return $user->id === $jobInvitation->talent_user_id || $user->id === $jobInvitation->employer_user_id
                        || $user?->role->is_owner
                        || $user?->role->is_admin
                        || $user?->role->hasPermission('delete-job-invitation');
            }
            return false;
        }

        /**
         * Determine whether the user can restore the record.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\JobInvitation  $jobInvitation
         * @return mixed
         */
        public function restore(User $user, JobInvitation $jobInvitation)
        {
            if ($user->tokenCan('access:talent') || $user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
                return $user->id === $jobInvitation->talent_user_id || $user->id === $jobInvitation->employer_user_id
                        || $user?->role->is_owner
                        || $user?->role->is_admin
                        || $user?->role->hasPermission('delete-job-invitation');
            }
            return false;
        }

        /**
         * Determine whether the user can permanently delete the record.
         *
         * @param  \App\Models\User  $user
         * @param  \App\Models\JobInvitation  $jobInvitation
         * @return mixed
         */
        public function forceDelete(User $user, JobInvitation $jobInvitation)
        {
            if ($user->tokenCan('access:talent') || $user->tokenCan('access:employer') || $user->tokenCan('access:admin')) {
                return $user->id === $jobInvitation->talent_user_id || $user->id === $jobInvitation->employer_user_id
                        || $user?->role->is_owner
                        || $user?->role->is_admin
                        || $user?->role->hasPermission('delete-job-invitation');
            }
            return false;
        }
    }
