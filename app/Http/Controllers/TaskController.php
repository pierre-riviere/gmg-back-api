<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Task::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // validate request input
            $validator = Validator::make($request->all(), Task::rulesStore);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "code" => "invalid_data",
                        "error" => $validator->errors()->all(),
                    ],
                    400
                );
            }

            // validate request input
            $validator = Validator::make($request->all(), Task::rulesStore);

            return response()->json(
                [
                    "code" => "created_task",
                    "task" => Task::create($request->all()),
                ],
                201
            );
        } catch (Exception $err) {
            return response()->json([
                "code" => "not_created_task",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return $task;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        try {
            // validate request input
            $validator = Validator::make($request->all(), Task::rulesUpdate);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "code" => "invalid_data",
                        "error" => $validator->errors()->all(),
                    ],
                    400
                );
            }

            // update task
            $task->update($request->all());

            return [
                "code" => "updated_task",
                "task" => $task,
            ];
        } catch (Exception $err) {
            return response()->json([
                "code" => "not_updated_task",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return [
            "code" => "deleted_task",
            "task" => $task,
        ];
    }

    /**
     * get tasks by userId
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $userId = $request->input("userId");
            if (!isset($userId)) {
                return [];
            }
            $user = User::find($userId);
            return $user ? $user->tasks->toArray() : [];
        } catch (Exception $err) {
            return response()->json([
                "code" => "error_tasks_list",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * store tasks for a specified user
     *
     * @param  \Illuminate\Http\Request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function storeTasks(Request $request, User $user)
    {
        try {
            $tasks = $request->input("tasks");
            $tasksObj = [];

            foreach ($tasks as $task) {
                $task["user_id"] = $user->id;

                $validator = Validator::make($task, Task::rulesStore);

                if ($validator->fails()) {
                    return response()->json(
                        [
                            "code" => "invalid_data",
                            "error" => $validator->errors()->all(),
                        ],
                        400
                    );
                }

                $tasksObj[] = new Task($task);
            }

            $user->tasks()->saveMany($tasksObj);

            return $tasksObj;
        } catch (Exception $err) {
            return response()->json([
                "code" => "error_store_tasks",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * update tasks for a specified user
     *
     * @param  \Illuminate\Http\Request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function updateTasks(Request $request, User $user)
    {
        try {
            $tasks = $request->input("tasks");
            $tasksObj = [];

            // validate tasks before updating
            foreach ($tasks as $task) {
                $task["user_id"] = $user->id;

                $rules = Task::rulesUpdate;
                $rules["id"] = ["required", "max:255"];
                $validator = Validator::make($task, $rules);

                if ($validator->fails()) {
                    return response()->json(
                        [
                            "code" => "invalid_data",
                            "error" => $validator->errors()->all(),
                        ],
                        400
                    );
                }

                // check if the task belongs to the current user
                if (
                    Task::where("id", $task["id"])
                        ->where("user_id", $user->id)
                        ->exists()
                ) {
                    $tasksObj[] = $task;
                }
            }

            // update tasks
            foreach ($tasksObj as $task) {
                Task::find($task["id"])->update($task);
            }

            return $tasksObj;
        } catch (Exception $err) {
            return response()->json([
                "code" => "error_update_tasks",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * delete tasks for a specified user
     *
     * @param  \Illuminate\Http\Request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function deleteTasks(Request $request, User $user)
    {
        try {
            $tasks = $request->input("tasks", []);

            $user
                ->tasks()
                ->whereIn("id", $tasks)
                ->delete();

            return [
                "code" => "deleted_tasks",
                "tasks" => $tasks,
            ];
        } catch (Throwable $err) {
            return response()->json([
                "code" => "error_delete_tasks",
                "error" => $err->getMessage(),
            ]);
        }
    }
}
