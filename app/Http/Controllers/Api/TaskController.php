<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
        $this->middleware(['throttle:10,1'])->except('index');
    }

    /**
     * @OA\Get(
     *   path="/api/tasks",
     *   summary="Get list of tasks",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\Response(response=200, description="Successful operation"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function index()
    {
        $tasks = auth()->user()->tasks()->with('category','comments') ->paginate(2);
        return TaskResource::collection($tasks);

    }

    /**
     * @OA\Post(
     *   path="/api/tasks",
     *   summary="Create a new task",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"title", "category_id", "due_date"},
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="category_id", type="integer"),
     *       @OA\Property(property="due_date", type="string", format="date")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Task Created Successfully"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Error try again")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'category_id' => 'required',
            'due_date' => 'required|date|date_format:Y-m-d|after_or_equal:' . date('Y-m-d')
        ]);

        $category = Category::findOrFail($validatedData['category_id']);

        if (auth()->id() != $category->user_id) {
            return response()->json([
                'message' => 'You don\'t own this category id: ' . $category->id,
                'Category title' => $category->title
            ], 401);
        }
        $request['user_id'] = auth()->id();
        $task = $category->tasks()->create($request->all());
        if ($task) {
            return response()->json([
                'message' => 'Task Created Successfully',
                'task' => $task,
            ], 201);
        }
        return response()->json(['message' => 'Error try again'], 500);
    }

    /**
     * @OA\Get(
     *   path="/api/tasks/{task}",
     *   summary="Get a specific task",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="task",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Successful operation"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Task not found")
     * )
     */
    public function show(Task $task)
    {
        if (auth()->id() != $task->user_id) {
            return response()->json(['message' => 'You don\'t own this resource']);
        }
        $task->load('category','comments','files');
        return new TaskResource($task);
    }

    /**
     * @OA\Put(
     *   path="/api/tasks/{task}",
     *   summary="Update a specific task",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="task",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"title", "category_id"},
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="category_id", type="integer"),
     *       @OA\Property(property="due_date", type="string", format="date")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Task Updated Successfully"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Error try again")
     * )
     */
    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'category_id' => 'required',
            'due_date' => 'date|date_format:Y-m-d',
        ]);

        $category = Category::findOrFail($validatedData['category_id']);

        if (auth()->id() != $category->user_id || auth()->id() != $task->user_id) {
            return response()->json([
                'message' => 'You don\'t own this category id: ' . $category->id,
                'Category title' => $category->title,
                'message2' => 'You don\'t have this task: ' . $task->id,
                'Task title' => $task->title
            ], 401);
        }
        $updated_task = $task->update($request->all());
        if ($updated_task) {
            return response()->json([
                'message' => 'Task Updated Successfully',
                'updated_task' => $task,
            ], 201);
        }
        return response()->json(['message' => 'Error try again'], 500);
    }

    /**
     * @OA\Delete(
     *   path="/api/tasks/{task}",
     *   summary="Delete a specific task",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="task",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Task Deleted Successfully"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Error try again")
     * )
     */
    public function destroy(Task $task)
    {
        if (auth()->id() != $task->user_id) {
            return response()->json(['message' => 'You don\'t have permission'], 401);
        }
        if ($task->delete()) {
            return response()->json(['message' => 'Task Deleted Successfully']);
        }
        return response()->json(['message' => 'Error try again later'], 500);
    }

    /**
     * @OA\Post(
     *   path="/api/tasks/{taskId}/restore",
     *   summary="Restore a deleted task",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="taskId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Restored Task Successfully"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Error try again")
     * )
     */
    public function restore($taskId)
    {
        $task = Task::withTrashed()->findOrFail($taskId);

        if (auth()->id() != $task->user_id) {
            return response()->json(['message' => 'You don\'t own this task'], 401);
        }

        if ($task->restore()) {
            return ['message' => 'Restored Task Successfully'];
        }
        return response()->json(['message' => 'You have an error'], 500);
    }

    /**
     * @OA\Delete(
     *   path="/api/tasks/{taskId}/force-delete",
     *   summary="Permanently delete a task",
     *   security={{"bearerAuth":{}}},
     *   tags={"Tasks"},
     *   @OA\Parameter(
     *     name="taskId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Task force deleted Successfully"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Error try again")
     * )
     */


    public function forceDelete($taskId)
    {
        $task = Task::withTrashed()->findOrFail($taskId);

        // Check if the authenticated user owns the category
        if (auth()->id() != $task->user_id) {
            return response()->json(['message' => 'You don\'t own this task'], 401);
        }

        if ($task->forceDelete()) {
            Storage::deleteDirectory('public/tasks/'.$task->id);
            return response()->json([
                'message' => 'Task force deleted Successfully',
            ], 201);
        }

        return response()->json(['message' => 'Error try again'], 500);
    }

}
