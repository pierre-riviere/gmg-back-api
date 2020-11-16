<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("tasks/list", [TaskController::class, "list"]);

Route::post("users/{user}/tasks", [TaskController::class, "storeTasks"]);
Route::put("users/{user}/tasks", [TaskController::class, "updateTasks"]);
Route::post("users/{user}/deleteTasks", [TaskController::class, "deleteTasks"]);

Route::resources([
    "users" => UserController::class,
    "tasks" => TaskController::class,
]);
