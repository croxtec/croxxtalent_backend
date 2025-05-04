<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\Project\Project;
use App\Models\Project\ProjectTeam;
use Closure;
use Illuminate\Http\Request;

class ProjectAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $requiredRole  - Optional parameter to require specific role ('lead' or null)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $requiredRole = null)
    {
        $user = $request->user();

        // If not a talent user, proceed normally
        if ($user->user_type !== 'talent') {
            return $next($request);
        }

        // Get project from route parameter or request input
        $project = $this->resolveProject($request);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found'
            ], 404);
        }

        // Get employee
        $employee = Employee::where('user_id', $user->id)
            ->where('id', $user->default_company_id)
            ->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access: Employee not found or mismatched company'
            ], 403);
        }

        // Check if employee is assigned to project
        $projectTeam = ProjectTeam::where('employee_id', $employee->id)
            ->where('project_id', $project->id)
            ->first();

        if (!$projectTeam) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access: You are not assigned to this project'
            ], 403);
        }

        // Check if team lead role is required
        if ($requiredRole === 'lead' && !$projectTeam->is_team_lead) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized Access: This action requires team lead permissions'
            ], 403);
        }

        // Add project and employee to request for later use
        $request->merge([
            'validated_project' => $project,
            'validated_employee' => $employee
        ]);

        return $next($request);
    }

    /**
     * Resolve the project from the request
     */
    private function resolveProject(Request $request)
    {
        // Check different ways to get project ID
        $projectId = $request->input('project_id');
        $projectCode = $request->input('pcode');
        $goalId = $request->route('goal') ?? $request->route('id');

        if ($projectId) {
            return Project::find($projectId);
        }

        if ($projectCode) {
            return Project::where('code', $projectCode)->first();
        }

        if ($goalId) {
            $goal = \App\Models\Project\ProjectGoal::find($goalId);
            return $goal ? $goal->project : null;
        }

        return null;
    }
}