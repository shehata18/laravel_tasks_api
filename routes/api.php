<?php

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login']);
//-----------------------------------------------------
Route::middleware(['auth:api','throttle:10,1'])->prefix('user')->group(function(){

    Route::post('update/password',[UserController::class,'updatePassword']);
    Route::post('update/profile',[UserController::class,'updateProfile']);

});
// Categories Routes
Route::resource('categories',CategoryController::class);
Route::put('categories/{categoryId}/restore',[CategoryController::class,'restore']);
Route::delete('categories/{categoryId}/force-delete',[CategoryController::class,'forceDelete']);

// Tasks Routes
Route::resource('tasks',TaskController::class);
Route::put('tasks/{taskId}/restore',[TaskController::class,'restore']);
Route::delete('tasks/{taskId}/force-delete',[TaskController::class,'forceDelete']);

// Comments Routes
Route::resource('comments',CommentController::class);

// Files Routes
Route::post('tasks/{taskId}/upload-file',[FileController::class,'upload']);
Route::delete('files/{file}',[FileController::class,'destroy']);

//Route::middleware('auth:api')->get('test_token',function(){
//    return auth()->user();
//});
