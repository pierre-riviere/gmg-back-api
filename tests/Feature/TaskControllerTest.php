<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Task;
use App\Models\User;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_ROUTE = "/api/tasks/";
    private const BASE_ROUTE_USERS = "/api/users/";

    /**
     * Test Task properties
     *
     * @param array $expectedTask
     * @param array $actualTask
     */
    private function assertTaskEquals(array $expectedTask, array $actualTask)
    {
        $expectedKeys = [
            "id" => false,
            "name" => true,
            "description" => true,
            "status" => true,
            "updated_at" => false,
            "created_at" => false,
        ];

        foreach ($expectedKeys as $key => $checkValue) {
            if ($checkValue) {
                $this->assertEquals(
                    $expectedTask[$key],
                    $actualTask[$key],
                    "$key should be equal"
                );
            } else {
                $this->assertArrayHasKey(
                    $key,
                    $actualTask,
                    "$key should exist"
                );
            }
        }
    }

    /**
     * Assert response task create/update
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $status
     * @param string $code
     * @param array $expectedTask
     * @param array $expectedMissingTask
     */
    private function assertTaskUpdate(
        object $response,
        int $status,
        string $code,
        array $expectedTask = null,
        array $expectedMissingTask = null
    ) {
        $response->assertStatus($status);
        $res = $response->json();
        $this->assertEquals($code, $res["code"]);
        $this->assertNotEmpty($res["error"]);
        if (!empty($expectedUser)) {
            $this->assertDatabaseHas("tasks", $expectedTask);
        }
        if (!empty($expectedMissingTask)) {
            $this->assertDatabaseMissing("tasks", $expectedMissingTask);
        }
    }

    /**
     * Assert response user update
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $status
     * @param string $code
     * @param array $expectedUser
     * @param array $expectedMissingUser
     */
    private function assertUserUpdate(
        object $response,
        int $status,
        string $code,
        array $expectedUser = null,
        array $expectedMissingUser = null
    ) {
        $response->assertStatus($status);
        $res = $response->json();
        $this->assertEquals($code, $res["code"]);
        $this->assertNotEmpty($res["error"]);
        if (!empty($expectedUser)) {
            $this->assertDatabaseHas("users", $expectedUser);
        }
        if (!empty($expectedMissingUser)) {
            $this->assertDatabaseMissing("users", $expectedMissingUser);
        }
    }

    /**
     * should get task by id
     * @group get_task
     * @return void
     */
    public function testShouldGetTask()
    {
        $users = User::factory(2)->create();
        $user = $users[1];
        $task = Task::factory()->create(["user_id" => $user->id]);
        $response = $this->get(self::BASE_ROUTE . $task->id);
        $response->assertStatus(200);
        $res = $response->json();
        $expectedTask = $task->toArray();
        $this->assertEquals($expectedTask, $res);
    }

    /**
     * should fail getting task when given invalid id
     * @group get_task
     * @return void
     */
    public function testShouldFailGetTaskWhenGivenInvalidId()
    {
        $response = $this->get(self::BASE_ROUTE . "1");
        $response->assertStatus(404);
    }

    /**
     * should get all tasks
     * @group get_task
     * @return void
     */
    public function testShouldGetAllTasks()
    {
        $tasks = Task::factory()
            ->count(3)
            ->create();
        $response = $this->get(self::BASE_ROUTE);
        $response->assertStatus(200);
        $res = $response->json();

        $expectedRes = $tasks->toArray();
        $this->assertEquals($expectedRes, $res);
    }

    /**
     * should create task
     * @group create_task
     * @return void
     */
    public function testShouldCreateTask()
    {
        $user = User::factory()->create();
        $task = [
            "name" => "task test",
            "description" => "desc test",
            "status" => "created",
            "user_id" => $user->id,
        ];
        $response = $this->post(self::BASE_ROUTE, $task);
        $response->assertStatus(201);
        $res = $response->json();
        $this->assertTaskEquals($task, $res["task"]);
        $this->assertDatabaseHas("tasks", $task);
    }

    /**
     * should fail creating a task when missing name
     * @group create_task
     * @return void
     */
    public function testFailCreateTaskWhenMissingName()
    {
        $user = User::factory()->create();
        $createData = [
            "description" => "desc test",
            "status" => "created",
            "user_id" => $user->id,
        ];
        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * should fail creating a task when missing description
     * @group create_task
     * @return void
     */
    public function testFailCreateTaskWhenMissingDescription()
    {
        $user = User::factory()->create();
        $createData = [
            "name" => "name test",
            "status" => "created",
            "user_id" => $user->id,
        ];
        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * should fail creating a task when missing status
     * @group create_task
     * @return void
     */
    public function testFailCreateTaskWhenMissingStatus()
    {
        $user = User::factory()->create();
        $createData = [
            "name" => "name test",
            "description" => "desc test",
            "user_id" => $user->id,
        ];
        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * should fail creating a task when missing user_id
     * @group create_task
     * @return void
     */
    public function testFailCreateTaskWhenMissingUserId()
    {
        $createData = [
            "name" => "name test",
            "description" => "desc test",
            "status" => "created",
        ];
        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * should fail creating a task when given invalid user_id
     * @group create_task
     * @return void
     */
    public function testFailCreateTaskWhenGivenInvalidUserId()
    {
        $createData = [
            "name" => "name test",
            "description" => "desc test",
            "status" => "created",
            "user_id" => 10,
        ];
        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * should update a task
     * @group update_task
     * @return void
     */
    public function testShouldUpdateTask()
    {
        $task = Task::factory()->create();

        $updateData = [
            "name" => "task name test",
            "description" => "task desc test",
            "status" => "task status",
        ];

        $response = $this->put(self::BASE_ROUTE . $task->id, $updateData);
        $response->assertStatus(200);
        $res = $response->json();

        $this->assertEquals("updated_task", $res["code"]);
        $expectedTask = array_merge($task->toArray(), $updateData);
        $this->assertTaskEquals($expectedTask, $res["task"]);
        $this->assertDatabaseHas("tasks", $updateData);
    }

    /**
     * should fail updating a task when given invalid id
     * @group update_task
     * @return void
     */
    public function testFailUpdateTaskWhenGivenInvalidId()
    {
        $updateData = [
            "name" => "task name test",
            "description" => "task desc test",
            "status" => "task status",
        ];

        $response = $this->put(self::BASE_ROUTE . "10", $updateData);
        $response->assertStatus(404);
    }

    /**
     * should fail updating a task when given empty name
     * @group update_task
     * @return void
     */
    public function testFailUpdateTaskWhenGivenEmptyName()
    {
        $task = Task::factory()->create();

        $updateData = [
            "name" => "",
            "description" => "task desc test",
            "status" => "task status",
        ];

        $response = $this->put(self::BASE_ROUTE . $task->id, $updateData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            $task->toArray()
        );
    }

    /**
     * should fail updating a task when given empty description
     * @group update_task
     * @return void
     */
    public function testFailUpdateTaskWhenGivenEmptyDescription()
    {
        $task = Task::factory()->create();

        $updateData = [
            "name" => "task name test",
            "description" => "",
            "status" => "task status",
        ];

        $response = $this->put(self::BASE_ROUTE . $task->id, $updateData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            $task->toArray()
        );
    }

    /**
     * should fail updating a task when given empty status
     * @group update_task
     * @return void
     */
    public function testFailUpdateTaskWhenGivenEmptyStatus()
    {
        $task = Task::factory()->create();

        $updateData = [
            "name" => "task name test",
            "description" => "task desc test",
            "status" => "",
        ];

        $response = $this->put(self::BASE_ROUTE . $task->id, $updateData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            $task->toArray()
        );
    }

    /**
     * should fail updating a task when given invalid user_id
     * @group update_task
     * @return void
     */
    public function testFailUpdateTaskWhenGivenInvalidUserId()
    {
        $task = Task::factory()->create();

        $updateData = [
            "name" => "task name test",
            "description" => "task desc test",
            "status" => "task status test",
            "user_id" => 10,
        ];

        $response = $this->put(self::BASE_ROUTE . $task->id, $updateData);

        $this->assertTaskUpdate(
            $response,
            400,
            "invalid_data",
            $task->toArray()
        );
    }

    /**
     * should delete a task
     * @group delete_task
     * @return void
     */
    public function testDeleteTask()
    {
        $task = Task::factory()->create();
        $response = $this->delete(self::BASE_ROUTE . $task->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing("tasks", ["id" => $task->id]);
    }

    /**
     * should fail deleting a task when given invalid id
     * @group delete_task
     * @return void
     */
    public function testFailDeleteTaskWhenInvalidId()
    {
        $task = Task::factory()->create();
        $response = $this->delete(self::BASE_ROUTE . "10");
        $response->assertStatus(404);
        $this->assertDatabaseHas("tasks", ["id" => $task->id]);
    }

    /**
     * should get tasks of a specified user
     * @group list_tasks
     * @return void
     */
    public function testShouldGetTasksByUser()
    {
        $tasks = Task::factory(3)->create();
        $task = $tasks[1];
        $response = $this->get(
            self::BASE_ROUTE . "list?userId=" . $task->user_id
        );
        $response->assertStatus(200);
        $res = $response->json();
        $expectedRes = [$task->toArray()];
        $this->assertEquals($expectedRes, $res);
    }

    /**
     * should get none task of a user which doesn't have task
     * @group list_tasks
     * @return void
     */
    public function testShouldGetNoneTaskForUserWithoutTask()
    {
        $user = User::factory()->create();
        $response = $this->get(self::BASE_ROUTE . "list?userId=" . $user->id);
        $response->assertStatus(200);
        $res = $response->json();
        $this->assertEquals([], $res);
    }

    /**
     * should get none task of a not existed user
     * @group list_tasks
     * @return void
     */
    public function testShouldGetNoneTaskWhenGivenNotExistedUser()
    {
        $userId = "10";
        $response = $this->get(self::BASE_ROUTE . "list?userId=" . $userId);
        $response->assertStatus(200);
        $res = $response->json();
        $this->assertEquals([], $res);
    }

    /**
     * should store tasks for a specified user
     * @group list_tasks
     * @return void
     */
    public function testShouldStoreUserTasks()
    {
        $users = User::factory(3)->create();
        $user = $users[1];
        $tasks = [
            [
                "name" => "task name 1",
                "description" => "task desc 1",
                "status" => "task status 1",
            ],
            [
                "name" => "task name 2",
                "description" => "task desc 2",
                "status" => "task status 2",
            ],
            [
                "name" => "task name 3",
                "description" => "task desc 3",
                "status" => "task status 3",
            ],
        ];

        $createData["tasks"] = $tasks;
        $response = $this->post(
            self::BASE_ROUTE_USERS . "$user->id/tasks",
            $createData
        );
        $response->assertStatus(200);
        $res = $response->json();

        foreach ($res as $key => $task) {
            $expectedTask = $tasks[$key];
            $this->assertEquals($expectedTask["name"], $task["name"]);
            $this->assertEquals(
                $expectedTask["description"],
                $task["description"]
            );
            $this->assertEquals($expectedTask["status"], $task["status"]);
            $this->assertEquals($user->id, $task["user_id"]);
            $this->assertNotEmpty($task["created_at"]);
            $this->assertNotEmpty($task["updated_at"]);
        }

        foreach ($tasks as $task) {
            $task["user_id"] = $user->id;
            $this->assertDatabaseHas("tasks", $task);
        }
    }

    /**
     * should fail store tasks for a specified user when given invalid task
     * @group list_tasks
     * @return void
     */
    public function testShouldFailStoreUserTasksWhenGivenInvalidTask()
    {
        $users = User::factory(3)->create();
        $user = $users[1];
        $userId = $user->id;

        // invalid task #2 : missing task name
        $tasks = [
            [
                "name" => "task name 1",
                "description" => "task desc 1",
                "status" => "task status 1",
            ],
            [
                "description" => "task desc 2",
                "status" => "task status 2",
            ],
            [
                "name" => "task name 3",
                "description" => "task desc 3",
                "status" => "task status 3",
            ],
        ];

        $createData["tasks"] = $tasks;
        $response = $this->post(
            self::BASE_ROUTE_USERS . "$userId/tasks",
            $createData
        );
        $response->assertStatus(400);

        foreach ($tasks as $task) {
            $task["user_id"] = $userId;
            $this->assertDatabaseMissing("tasks", $task);
        }

        $this->assertDatabaseMissing("tasks", ["user_id" => $userId]);
    }

    /**
     * should fail store tasks for a specified user when given invalid user
     * @group list_tasks
     * @return void
     */
    public function testShouldFailStoreUserTasksWhenGivenInvalidUser()
    {
        $invalidUserId = "10";

        // invalid task #2 : missing task name
        $tasks = [
            [
                "name" => "task name 1",
                "description" => "task desc 1",
                "status" => "task status 1",
            ],
            [
                "description" => "task desc 2",
                "status" => "task status 2",
            ],
            [
                "name" => "task name 3",
                "description" => "task desc 3",
                "status" => "task status 3",
            ],
        ];

        $createData["tasks"] = $tasks;
        $response = $this->post(
            self::BASE_ROUTE_USERS . "$invalidUserId/tasks",
            $createData
        );
        $response->assertStatus(404);

        foreach ($tasks as $task) {
            $task["user_id"] = $invalidUserId;
            $this->assertDatabaseMissing("tasks", $task);
        }
    }

    /**
     * should update specified user tasks
     * @group update_user_tasks
     * @return void
     */
    public function testShouldUpdateUserTasks()
    {
        $users = User::factory(3)->create();
        $user = $users[1];
        $userId = $user->id;

        $tasks = Task::factory(3)->create(["user_id" => $userId]);

        $updateTasks = [
            [
                "id" => $tasks[0]->id,
                "name" => "task name 1",
                "description" => "task desc 1",
                "status" => "task status 1",
            ],
            [
                "id" => $tasks[1]->id,
                "name" => "task name 2",
                "description" => "task desc 2",
                "status" => "task status 2",
            ],
            [
                "id" => $tasks[2]->id,
                "name" => "task name 3",
                "description" => "task desc 3",
                "status" => "task status 3",
            ],
        ];

        $createData["tasks"] = $updateTasks;
        $response = $this->put(
            self::BASE_ROUTE_USERS . "$userId/tasks",
            $createData
        );
        $response->assertStatus(200);
        $res = $response->json();

        $expectedTasks = array_map(function ($task) use ($userId) {
            $task["user_id"] = $userId;
            return $task;
        }, $updateTasks);

        $this->assertEquals($expectedTasks, $res);

        foreach ($expectedTasks as $task) {
            $this->assertDatabaseHas("tasks", $task);
        }
    }

    /**
     * should fail update specified user tasks when given invalid user
     * @group update_user_tasks
     * @return void
     */
    public function testShouldFailUpdateUserTasksWhenGivenInvalidUser()
    {
        // invalid user id (not existed)
        $userId = "10";

        $updateTasks = [
            [
                "id" => 1,
                "name" => "task name 1",
                "description" => "task desc 1",
                "status" => "task status 1",
            ],
            [
                "id" => 2,
                "name" => "task name 2",
                "description" => "task desc 2",
                "status" => "task status 2",
            ],
            [
                "id" => 3,
                "name" => "task name 3",
                "description" => "task desc 3",
                "status" => "task status 3",
            ],
        ];

        $createData["tasks"] = $updateTasks;
        $response = $this->put(
            self::BASE_ROUTE_USERS . "$userId/tasks",
            $createData
        );
        $response->assertStatus(404);

        foreach ($updateTasks as $task) {
            $task["user_id"] = $userId;
            $this->assertDatabaseMissing("tasks", $task);
        }
    }

    /**
     * should fail update specified user tasks when given invalid tasks
     * @group update_user_tasks
     * @return void
     */
    public function testShouldFailUpdateUserTasksWhenGivenInvalidTasks()
    {
        $users = User::factory(3)->create();
        $user = $users[1];
        $userId = $user->id;
        $tasks = Task::factory(3)->create(["user_id" => $userId]);

        // invalid task #2 : empty name
        $updateTasks = [
            [
                "id" => $userId,
                "name" => "task name 1",
                "description" => "task desc 1",
                "status" => "task status 1",
            ],
            [
                "id" => $userId,
                "name" => "",
                "description" => "task desc 2",
                "status" => "task status 2",
            ],
            [
                "id" => $userId,
                "name" => "task name 3",
                "description" => "task desc 3",
                "status" => "task status 3",
            ],
        ];

        $createData["tasks"] = $updateTasks;
        $response = $this->put(
            self::BASE_ROUTE_USERS . "$userId/tasks",
            $createData
        );
        $response->assertStatus(400);

        foreach ($updateTasks as $task) {
            $task["user_id"] = $userId;
            $this->assertDatabaseMissing("tasks", $task);
        }

        foreach ($tasks as $task) {
            $this->assertDatabaseHas("tasks", $task->toArray());
        }
    }

    /**
     * should delete specified user tasks
     * @group delete_user_tasks
     * @return void
     */
    public function testShouldDeleteUserTasks()
    {
        $users = User::factory(3)->create();
        $user = $users[1];
        $userId = $user->id;

        $tasks = Task::factory(5)->create(["user_id" => $userId]);

        $taskIds = $tasks->reduce(function ($memo, $task) {
            $memo[] = $task->id;
            return $memo;
        }, []);

        $deleteTaskIds = [$tasks[1]->id, $tasks[2]->id];
        $keepTaskIds = array_diff($taskIds, $deleteTaskIds);

        $deleteTasks["tasks"] = $deleteTaskIds;

        $response = $this->delete(
            self::BASE_ROUTE_USERS . "$userId/tasks",
            $deleteTasks
        );

        $response->assertStatus(200);
        $res = $response->json();

        $expectedRes = [
            "code" => "deleted_tasks",
            "tasks" => $deleteTaskIds,
        ];

        $this->assertEquals($expectedRes, $res);

        foreach ($deleteTaskIds as $taskId) {
            $this->assertDatabaseMissing("tasks", [
                "id" => $deleteTaskIds,
            ]);
        }

        foreach ($keepTaskIds as $taskId) {
            $this->assertDatabaseHas("tasks", [
                "user_id" => $userId,
                "id" => $taskId,
            ]);
        }
    }

    /**
     * should fail delete specified user tasks when given invalid user
     * @group delete_user_tasks
     * @return void
     */
    public function testFailDeleteUserTasksWhenGivenInvalidUser()
    {
        $userId = "10";
        $deleteTasks["tasks"] = [];

        $response = $this->delete(
            self::BASE_ROUTE_USERS . "$userId/tasks",
            $deleteTasks
        );

        $response->assertStatus(404);
    }
}
