<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\CroxxFeedback as Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedback = Feedback::with('user')->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:suggestion,bug,question,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'type' => $request->type,
            'status' => 'new'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ], 201);
    }

    public function show($id)
    {
        $feedback = Feedback::with('user')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'subject' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:suggestion,bug,question,other',
            'status' => 'sometimes|required|in:new,in-progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback->update($request->only(['subject', 'message', 'type', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Feedback updated successfully',
            'data' => $feedback
        ]);
    }

    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully'
        ]);
    }
}