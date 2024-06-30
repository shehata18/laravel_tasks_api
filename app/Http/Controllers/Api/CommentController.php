<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'task_id'=>'required',
            'body'=>'required'
        ]);
        $validatedData['user_id'] = auth()->id();
        $task = Task::findOrFail($request->task_id);
        if(auth()->id() != $task->user_id){
            return response()->json([
                'message' => 'You don\'t own this task id: ' . $task->id,
                'task title' => $task->title
            ], 401);

        }
        $comment = $task->comments()->create($validatedData);
        if ($comment) {
            $userComments = $task->comments()->where('user_id', auth()->id())->get();
            return response()->json([
                'message' => 'Comment Created Successfully',
                'created_comment' => $comment,
                'user_comments' => $userComments,
            ], 201);
        }
        return response()->json(['message' => 'Error try again'], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $validatedData = $request->validate([
           'body'=>'required',
        ]);

        if(auth()->id() != $comment->user_id) {
            return response()->json([
                'message' => 'You don\'t own this Comment id: ' . $comment->id,
                'comment body' => $comment->body
            ], 401);
        }
        $updated = $comment->update($validatedData);
        if ($updated) {
            $userComments = $comment->where('user_id', auth()->id())->get();
            return response()->json([
                'message' => 'Comment Updated Successfully',
                'updated_comment' => $comment->body,
                'user_comments'=>$userComments
            ], 201);
        }
        return response()->json(['message' => 'Error try again'], 500);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        // Check if the authenticated user owns the category
        if (auth()->id() != $comment->user_id) {
            return response()->json(['message' => 'You don\'t own this Comment'], 401);
        }
        if($comment->delete()){
            return ['message'=>'comment deleted successfully','deleted_comment'=>$comment];
        }
        return response()->json(['message' => 'Error try again'], 500);

    }
}
