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

class TaskCommentController extends Controller
{
    /**
     * Constructor with middleware registration
     */
    public function __construct()
    {
        // Apply basic project access middleware to all methods
        // Any team member can comment (no team lead requirement)
        $this->middleware('project.access');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Get the employee record for the current user
        $employee = Employee::where('user_id', $user->id)
            ->where('id', $user->default_company_id)
            ->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee record not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'comment' => 'required|between:3,256',
            'goal_id' => 'required|exists:project_goals,id'
        ]);

        // Use the actual employee ID
        $validatedData['employee_id'] = $employee->id;

        $comment = GoalComment::create($validatedData);

        // Track comment creation activity
        GoalActivity::create([
            'goal_id' => $validatedData['goal_id'],
            'activity_type' => 'comment_add',
            'description' => 'Comment added to goal',
            'performed_by' => $user->id
        ]);

        return response()->json([
            'status' => true,
            'message' => "Comment added successfully",
            'data' => $comment,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Get employee and project information
            $employee = Employee::where('user_id', $user->id)
                ->where('id', $user->default_company_id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $validatedData = $request->validate([
                'comment' => 'required|between:3,256',
            ]);

            $comment = GoalComment::findOrFail($id);

            // Get the goal to determine the project
            $goal = ProjectGoal::findOrFail($comment->goal_id);

            // Check if the comment belongs to this employee or if they're a team lead
            $projectTeam = ProjectTeam::where('employee_id', $employee->id)
                ->where('project_id', $goal->project_id)
                ->first();

            if ($comment->employee_id !== $employee->id && (!$projectTeam || !$projectTeam->is_team_lead)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this comment',
                ], 403);
            }

            $comment->update($validatedData);

            // Track comment update activity
            GoalActivity::create([
                'goal_id' => $comment->goal_id,
                'activity_type' => 'comment_update',
                'description' => 'Comment updated',
                'performed_by' => $user->id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Comment updated successfully',
                'data' => $comment->fresh(),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Comment not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update comment',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Get employee information
            $employee = Employee::where('user_id', $user->id)
                ->where('id', $user->default_company_id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $comment = GoalComment::findOrFail($id);

            // Get the goal to determine the project
            $goal = ProjectGoal::findOrFail($comment->goal_id);

            // Check if the comment belongs to this employee or if they're a team lead
            $projectTeam = ProjectTeam::where('employee_id', $employee->id)
                ->where('project_id', $goal->project_id)
                ->first();

            if ($comment->employee_id !== $employee->id && (!$projectTeam || !$projectTeam->is_team_lead)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete this comment',
                ], 403);
            }

            $goalId = $comment->goal_id;
            $comment->delete();

            // Track comment deletion activity
            GoalActivity::create([
                'goal_id' => $goalId,
                'activity_type' => 'comment_delete',
                'description' => 'Comment deleted from goal',
                'performed_by' => $user->id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Comment deleted successfully',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Comment not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete comment',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}