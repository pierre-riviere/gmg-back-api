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
     *
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
     *
     * @return void
     */
    public function testShouldFailGfettingTaskWhenGivenInvalidId()
    {
        $response = $this->get(self::BASE_ROUTE . "1");
        $response->assertStatus(404);
    }

    /**
     * should get all tasks
     *
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
     *
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
     *
     * @return void
     */
    public function testFailCreatingTaskWhenMissingName()
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
     *
     * @return void
     */
    public function testFailCreatingTaskWhenMissingDescription()
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
     *
     * @return void
     */
    public function testFailCreatingTaskWhenMissingStatus()
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
     *
     * @return void
     */
    public function testFailCreatingTaskWhenMissingUserId()
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
     *
     * @return void
     */
    public function testFailCreatingTaskWhenGivenInvalidUserId()
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
     *
     * @return void
     */
    public function testShouldUpdateTask()
    {
    }

    /**
     * should fail updating a task when given invalid id
     *
     * @return void
     */
    public function testFailUpdatingTaskWhenWhenGivenInvalidId()
    {
    }

    /**
     * should fail updating a task when given empty name
     *
     * @return void
     */
    public function testFailUpdatingTaskWhenWhenGivenEmptyName()
    {
    }

    /**
     * should fail updating a task when given empty description
     *
     * @return void
     */
    public function testFailUpdatingTaskWhenWhenGivenEmptyDescription()
    {
    }

    /**
     * should fail updating a task when given empty status
     *
     * @return void
     */
    public function testFailUpdatingTaskWhenWhenGivenEmptyStatus()
    {
    }

    /**
     * should fail updating a task when given invalid user_id
     *
     * @return void
     */
    public function testFailUpdatingTaskWhenWhenGivenInvalidUserId()
    {
    }

    /**
     * should delete a task
     *
     * @return void
     */
    public function testDeleteTask()
    {
    }

    /**
     * should fail deleting a task when given invalid id
     *
     * @return void
     */
    public function testFailDeltingTaskWhenInvalidId()
    {
    }
}
