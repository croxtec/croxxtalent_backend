<?php

namespace App\Http\Controllers\Api\v2\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\GoalActivity;
use App\Models\Project\GoalComment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskCommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'comment' => 'required|between:3,256',
            'employee_id' => 'required',
            'goal_id' => 'required|exists:project_goals,id'
        ]);

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

            $validatedData = $request->validate([
                'comment' => 'required|between:3,256',
            ]);

            $comment = GoalComment::findOrFail($id);

            // Optional: Add authorization check
            // if ($comment->employee_id !== $user->id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized to update this comment',
            //     ], 403);
            // }

            $originalComment = $comment->comment;
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
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $comment = GoalComment::findOrFail($id);

            // Optional: Add authorization check
            // if ($comment->employee_id !== $user->id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized to delete this comment',
            //     ], 403);
            // }

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