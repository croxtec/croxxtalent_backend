<?php
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Cv;
use App\Models\Campaign;
use App\Models\JobInvitation;
// use App\Models\Project\Project:

if ( ! function_exists('app_url')) {
    function app_url(string $path = '')
    {
        // Url::forceRootUrl(config('myapp.url'));
        $slash = isset($path[0]) && $path[0] == '/' ? '' : '/';
        return config('myapp.url') . $slash . $path;
    }
}

if ( ! function_exists('api_url')) {
    function api_url(string $path)
    {
        $slash = $path[0] == '/' ? '' : '/';
        return config('myapp.api_url') . $slash . $path;
    }
}

if ( ! function_exists('client_url')) {
    function client_url(string $path)
    {
        $slash = $path[0] == '/' ? '' : '/';
        return config('myapp.client_url') . $slash . $path;
    }
}

if ( ! function_exists('cloud_asset')) {

    function cloud_asset(string $path)
    {
        // Storage::url($path);
        $custom_endpoint = config('filesystems.disks.do-spaces.custom_endpoint');
        return $custom_endpoint . '/' . $path;
    }
}

if ( ! function_exists('sign_imageboss_image')) {
    function sign_imageboss_image(string $imageboss_image_url)
    {
        $secret = config('myapp.imageboss_sign_token');

        $image_path = preg_replace("/https:\/\/img.imageboss.me/", '', $imageboss_image_url);

        $boss_token = hash_hmac('sha256', $image_path, $secret);
        return $imageboss_image_url . '?bossToken=' . $boss_token;
    }
}

if ( ! function_exists('image_to_data_url')) {
    function image_to_data_url($image_url)
    {
        try {
            $type = pathinfo($image_url, PATHINFO_EXTENSION);
            $data = file_get_contents($image_url);
            $base64_image = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64_image;
        } catch(ErrorException $e) {
            return null;
        }
    }
}


if ( ! function_exists('related_records_count')) {
    function related_records_count($model_name, $model)
    {
        // $relatedRecordsCount intentially initialized with "1" to avoid accidental
        // deletion of records where function is used.
        $relatedRecordsCount = 0;

        if ($model_name == 'App\Models\Degree') {
            $relatedRecordsCount = 0;
            $relatedRecordsCount += Campaign::where('minimum_degree_id', $model->id)->count();

        } elseif ($model_name == 'App\Models\User') {
            $relatedRecordsCount = 0;
            $relatedRecordsCount += JobInvitation::where('talent_user_id', $model->id)->count();
            $relatedRecordsCount += JobInvitation::where('employer_user_id', $model->id)->count();
            $relatedRecordsCount += Cv::where('user_id', $model->id)->count();

        } elseif ($model_name == 'App\Models\Cv') {
            $relatedRecordsCount = 0;
            $relatedRecordsCount += JobInvitation::where('talent_cv_id', $model->id)->count();

        } elseif ($model_name == 'App\Models\Campaign') {
            $relatedRecordsCount = 0;
            // No restrictions, allow permanent deletion of Campaign

        }

        return $relatedRecordsCount;
    }
}

if (! function_exists('croxxtalent_competency_tree') ){
    function croxxtalent_competency_tree($competency){
        $groups = array();
        $competence_tree = array();

        foreach($competency as $skill){
            $groups[$skill['domain_name']][$skill['core_name']][] = $skill;
        }

        foreach ($groups as $key => $skill) {
           $competence = [
                'name' => $key,
                'core' => []
            ];
            foreach ($skill as $ckey => $core) {
                $score  = array(
                    'name' => $ckey,
                    'skills' => $core
                );
                array_push( $competence['core'], $score );
           }
          array_push($competence_tree, $competence);
        }

        return $competence_tree;
    }
}

if (!function_exists('validateEmployeeAccess')) {
    /**
     * Validate if a user has access to a selected employee's company data or return unauthorized response.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Employee $selected_employee_company
     * @return \Illuminate\Http\JsonResponse|bool
     */
    function validateEmployeeAccess($user, $selected_employee_company)
    {
        // Get the user's current company
        $user_current_company = \App\Models\Employee::where('id', $user->default_company_id)
            ->where('user_id', $user->id)
            ->with('supervisor')
            ->first();

        // Check if the user's current company exists
        if (!$user_current_company) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access: User company not found'
            ], 400);
        }

        // Validate if the user's current company matches the selected employee's company
        if ($user_current_company->id === $selected_employee_company->id) {
            return true; // Access granted
        }

        // Validate supervisor permissions, if applicable
        if ($user_current_company->supervisor) {
            $supervisor = $user_current_company->supervisor;

            // Check role-based access
            if ($supervisor->type === 'role'
                && $selected_employee_company->department_role_id === $supervisor->department_role_id) {
                return true; // Access granted
            }

            // Check department-based access
            if ($supervisor->type === 'department'
                && $selected_employee_company->job_code_id === $supervisor->department_id) {
                return true; // Access granted
            }
        }

        // Unauthorized access
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized Access: Access to selected company denied'
        ], 400);
    }
}


if (!function_exists('validateProjectAccess')) {

    /**
     * Validates if the user has access to a project.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Project\Project $project
     * @return \Illuminate\Http\JsonResponse|null
     */
    function validateProjectAccess($user, $project)
    {
        if ($user->user_type === 'talent') {
            $employee = \App\Models\Employee::where('user_id', $user->id)
                ->where('id', $user->default_company_id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access: Employee not found or mismatched company'
                ], 403);
            }

            $isAssigned = \App\Models\Project\ProjectTeam::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access: You are not assigned to this project'
                ], 403);
            }
        }

        // Authorized
        return true;
    }
}


if (!function_exists('validateEmployerProjectOwnership')) {
    /**
     * Validates that the user is an employer and owns the project.
     *
     * @param \App\Models\User $user
     * @param string $projectCode
     * @return \App\Models\Project\Project|\Illuminate\Http\JsonResponse
     */
    function validateEmployerProjectOwnership($user, string $projectCode)
    {
        // Reject if user is not employer
        if ($user->type !== 'employer') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access: Only employers can perform this action.',
            ], 403);
        }

        // Ensure project belongs to this employer
        if(is_numeric($projectCode)){
            $project = \App\Models\Project\Project::where('id', $projectCode)
                ->where('employer_user_id', $user->id)
                ->first();
        }else{
            $project = \App\Models\Project\Project::where('code', $projectCode)
                ->where('employer_user_id', $user->id)
                ->first();
        }

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access: Project not found or not owned by you.',
            ], 403);
        }

        return $project;
    }
}
