<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
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
                "firstname" => ["required", "max:255"],
                "email" => ["required", "unique:users", "email"],
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
                    "code" => "created_user",
                    "user" => User::create($request->all()),
                ],
                201
            );
        } catch (Exception $err) {
            return response()->json([
                "code" => "not_created_user",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        try {
            // rules for user input validation
            $rules = [
                "name" => ["filled", "max:255"],
                "firstname" => ["filled", "max:255"],
                "email" => ["filled", "email"],
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

            // check if the email is not existed
            if (
                User::where("id", "<>", $user->id)
                    ->where("email", "=", $request->input("email"))
                    ->count() > 0
            ) {
                return response()->json(
                    [
                        "code" => "existed_email",
                        "error" => "The email address already exists.",
                    ],
                    400
                );
            }

            // update user
            $user->update($request->all());

            return [
                "code" => "updated_user",
                "user" => $user,
            ];
        } catch (Exception $err) {
            return response()->json([
                "code" => "not_updated_user",
                "error" => $err->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return [
            "code" => "deleted_user",
            "user" => $user,
        ];
    }
}
