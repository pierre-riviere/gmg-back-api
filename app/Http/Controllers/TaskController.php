<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Exception;
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
            $rules = [
                "name" => ["required", "max:255"],
                "description" => ["required", "max:255"],
                "status" => ["required", "max:255"],
                "user_id" => ["required", "exists:users,id"],
            ];

            // validate request input
            $validator = Validator::make($request->all(), $rules);

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
            $validator = Validator::make($request->all(), $rules);

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
            // rules for user input validation
            $rules = [
                "name" => ["filled", "max:255"],
                "description" => ["filled", "max:255"],
                "status" => ["filled", "max:255"],
                "user_id" => ["filled", "exists:users,id"],
            ];

            // validate request input
            $validator = Validator::make($request->all(), $rules);

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
}
