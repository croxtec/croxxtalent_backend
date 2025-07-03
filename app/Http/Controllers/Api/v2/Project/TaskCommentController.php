<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\GoalActivity;
use App\Models\Project\GoalComment;
use App\Models\Project\ProjectGoal;
use App\Models\Employee;
use App\Models\Project\ProjectTeam;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Traits\ApiResponseTrait;
use Symfony\Component\HttpFoundation\Response;

class TaskCommentController extends Controller
{
    use ApiResponseTrait;

    /**
     * Constructor with middleware registration
     */
    public function __construct()
    {
        $this->middleware('project.access');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)
            ->where('id', $user->default_company_id)
            ->first();

        if (!$employee) {
            return $this->errorResponse(
                'services.comments.employee_not_found',
                [],
                Response::HTTP_NOT_FOUND
            );
        }

        $validatedData = $request->validate([
            'comment' => 'required|between:3,256',
            'goal_id' => 'required|exists:project_goals,id'
        ]);

        $validatedData['employee_id'] = $employee->id;
        $comment = GoalComment::create($validatedData);

        GoalActivity::create([
            'goal_id' => $validatedData['goal_id'],
            'activity_type' => 'comment_add',
            'description' => __('services.activities.comment_added'),
            'performed_by' => $user->id
        ]);

        return $this->successResponse(
            $comment,
            'services.comments.added',
            [],
            Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $employee = Employee::where('user_id', $user->id)
                ->where('id', $user->default_company_id)
                ->first();

            if (!$employee) {
                return $this->errorResponse(
                    'services.comments.employee_not_found',
                    [],
                    Response::HTTP_NOT_FOUND
                );
            }

            $validatedData = $request->validate([
                'comment' => 'required|between:3,256',
            ]);

            $comment = GoalComment::findOrFail($id);
            $goal = ProjectGoal::findOrFail($comment->goal_id);

            $projectTeam = ProjectTeam::where('employee_id', $employee->id)
                ->where('project_id', $goal->project_id)
                ->first();

            if ($comment->employee_id !== $employee->id && (!$projectTeam || !$projectTeam->is_team_lead)) {
                return $this->errorResponse(
                    'services.comments.unauthorized',
                    [],
                    Response::HTTP_FORBIDDEN
                );
            }

            $comment->update($validatedData);

            GoalActivity::create([
                'goal_id' => $comment->goal_id,
                'activity_type' => 'comment_update',
                'description' => __('services.activities.comment_updated'),
                'performed_by' => $user->id
            ]);

            return $this->successResponse(
                $comment->fresh(),
                'services.comments.updated'
            );

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'services.comments.not_found',
                [],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.comments.update_error',
                ['error' => config('app.debug') ? $e->getMessage() : null],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $employee = Employee::where('user_id', $user->id)
                ->where('id', $user->default_company_id)
                ->first();

            if (!$employee) {
                return $this->errorResponse(
                    'services.comments.employee_not_found',
                    [],
                    Response::HTTP_NOT_FOUND
                );
            }

            $comment = GoalComment::findOrFail($id);
            $goal = ProjectGoal::findOrFail($comment->goal_id);

            $projectTeam = ProjectTeam::where('employee_id', $employee->id)
                ->where('project_id', $goal->project_id)
                ->first();

            if ($comment->employee_id !== $employee->id && (!$projectTeam || !$projectTeam->is_team_lead)) {
                return $this->errorResponse(
                    'services.comments.unauthorized',
                    [],
                    Response::HTTP_FORBIDDEN
                );
            }

            $goalId = $comment->goal_id;
            $comment->delete();

            GoalActivity::create([
                'goal_id' => $goalId,
                'activity_type' => 'comment_delete',
                'description' => __('services.activities.comment_deleted'),
                'performed_by' => $user->id
            ]);

            return $this->successResponse(
                null,
                'services.comments.deleted'
            );

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                'services.comments.not_found',
                [],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'services.comments.delete_error',
                ['error' => config('app.debug') ? $e->getMessage() : null],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}