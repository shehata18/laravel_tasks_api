<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function upload(Request $request, $taskId)
    {
        $request->validate([
            'file' => 'required|file|max:5000|mimes:jpg,jpeg,pdf,docs'
        ]);
        $task = Task::findOrFail($taskId);
        if (auth()->id() != $task->user_id) {
            return response()->json([
                'message' => 'You don\'t own this task id: ' . $task->id,
                'task title' => $task->title
            ], 401);
        }
        $fileName = $request->file('file')->hashName();
        $uploaded = $request->file('file')->storeAs('public/tasks/' . $task->id, $fileName);

        if($uploaded){
            $fileData = ['user_id'=>auth()->id(), 'name'=>$fileName];
            $savedFile = $task->files()->create($fileData);
            if ($savedFile){
                return new FileResource($savedFile);
            }
        }
        return response()->json(['message' => 'Error try again'], 500);

    }


    public function destroy(File $file)
    {
        if (auth()->id() != $file->user_id) {
            return response()->json([
                'message' => 'You don\'t own this task id: ' . $file->id,
                'file name' => $file->name
            ], 401);
        }
        if($file->delete()){
           $deleted = Storage::delete('public/tasks/'.$file->task_id.'/'.$file->name);

           if ($deleted){
               return [
                   'message'=>'file deleted successfully',
                    'file_name_deleted'=>$file->name

               ];
           }

        }
        return response()->json(['message' => 'Error try again'], 500);


    }
}
