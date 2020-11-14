<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_ROUTE = "/api/users/";

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test user properties
     *
     * @param array $expectedUser
     * @param array $actualUser
     */
    private function assertUserEquals(array $expectedUser, array $actualUser)
    {
        $expectedKeys = [
            "id" => false,
            "firstname" => true,
            "name" => true,
            "email" => true,
            "updated_at" => false,
            "created_at" => false,
        ];

        foreach ($expectedKeys as $key => $checkValue) {
            if ($checkValue) {
                $this->assertEquals(
                    $expectedUser[$key],
                    $actualUser[$key],
                    "$key should be equal"
                );
            } else {
                $this->assertArrayHasKey(
                    $key,
                    $actualUser,
                    "$key should exist"
                );
            }
        }
    }

    /**
     * Assert response user create/update
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
     * should get a user by id
     *
     * @return void
     */
    public function testShouldGetUser()
    {
        $user = User::factory()->create();
        $response = $this->get(self::BASE_ROUTE . $user->id);
        $response->assertStatus(200);
        $res = $response->json();
        $expectedRes = $user->toArray();
        $this->assertEquals($expectedRes, $res);
    }

    /**
     * should fail getting user when given invalid id
     *
     * @return void
     */
    public function testShouldFailGetUserWhenGivenInvalidId()
    {
        $response = $this->get(self::BASE_ROUTE . "1");
        $response->assertStatus(404);
    }

    /**
     * should get all users
     *
     * @return void
     */
    public function testShouldGetAllUsers()
    {
        $users = User::factory()
            ->count(3)
            ->create();
        $response = $this->get(self::BASE_ROUTE);
        $response->assertStatus(200);
        $res = $response->json();

        $expectedRes = $users->toArray();
        $this->assertEquals($expectedRes, $res);
    }

    /**
     * should create a user
     *
     * @return void
     */
    public function testShouldCreateUser()
    {
        $user = [
            "name" => "Doe",
            "firstname" => "John",
            "email" => "john.doe@mail.com",
        ];
        $response = $this->post(self::BASE_ROUTE, $user);
        $response->assertStatus(201);
        $res = $response->json();
        $this->assertUserEquals($user, $res["user"]);

        $this->assertDatabaseHas("users", $user);
    }

    /**
     * Should not create user when not given name
     *
     * @return void
     */
    public function testShouldFailCreateUserWhenEmptyName()
    {
        $createData = [
            "firstname" => "John",
            "email" => "john.doe@mail.com",
        ];

        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertUserUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * Should not create user when not given firstname
     *
     * @return void
     */
    public function testShouldFailCreateUserWhenEmptyFirstname()
    {
        $createData = [
            "name" => "Doe",
            "email" => "john.doe@mail.com",
        ];

        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertUserUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * Should not create user when given invalid email
     *
     * @return void
     */
    public function testShouldFailCreateUserWhenInvalidEmail()
    {
        $createData = [
            "name" => "Doe",
            "firstname" => "John",
            "email" => "invalidmail",
        ];

        $response = $this->post(self::BASE_ROUTE, $createData);

        $this->assertUserUpdate(
            $response,
            400,
            "invalid_data",
            [],
            $createData
        );
    }

    /**
     * Should update a user
     *
     * @return void
     */
    public function testShouldUpdateUser()
    {
        $user = User::factory()->create();

        $updateData = [
            "name" => "Updatedname",
            "firstname" => "Updatedfirstname",
            "email" => "updatedemail@mail.com",
        ];

        $response = $this->put(self::BASE_ROUTE . $user->id, $updateData);
        $response->assertStatus(200);
        $res = $response->json();

        $this->assertEquals("updated_user", $res["code"]);
        $expectedUser = array_merge($user->toArray(), $updateData);
        $this->assertUserEquals($expectedUser, $res["user"]);
        $this->assertDatabaseHas("users", $updateData);
    }

    /**
     * should fail updating user when given invalid id
     *
     * @return void
     */
    public function testShouldFailUpdateUserWhenGivenInvalidId()
    {
        $updateData = [
            "name" => "Updatedname",
            "firstname" => "Updatedfirstname",
            "email" => "updatedemail@mail.com",
        ];
        $response = $this->put(self::BASE_ROUTE . "10", $updateData);
        $response->assertStatus(404);
    }

    /**
     * Should not update user when not given name
     *
     * @return void
     */
    public function testShouldFailUpdateUserWhenEmptyName()
    {
        $user = User::factory()->create();

        $updateData = [
            "name" => "",
            "firstname" => "Updatedfirstname",
            "email" => "updatedemail@mail.com",
        ];

        $response = $this->put(self::BASE_ROUTE . $user->id, $updateData);

        $this->assertUserUpdate(
            $response,
            400,
            "invalid_data",
            $user->toArray()
        );
    }

    /**
     * Should not update user when not given firstname
     *
     * @return void
     */
    public function testShouldFailUpdateWhenEmptyFirstname()
    {
        $user = User::factory()->create();

        $updateData = [
            "name" => "",
            "firstname" => "Updatedfirstname",
            "email" => "updatedemail@mail.com",
        ];

        $response = $this->put(self::BASE_ROUTE . $user->id, $updateData);

        $this->assertUserUpdate(
            $response,
            400,
            "invalid_data",
            $user->toArray()
        );
    }

    /**
     * Should not update user when given invalid email
     *
     * @return void
     */
    public function testShouldFailUpdateWhenInvalidEmail()
    {
        $user = User::factory()->create();

        $updateData = [
            "name" => "Updatedname",
            "firstname" => "Updatedfirstname",
            "email" => "invalidemail",
        ];

        $response = $this->put(self::BASE_ROUTE . $user->id, $updateData);

        $this->assertUserUpdate(
            $response,
            400,
            "invalid_data",
            $user->toArray()
        );
    }

    /**
     * Should not update user when email address already exists
     *
     * @return void
     */
    public function testShouldFailUpdateWhenExistedEmail()
    {
        $users = User::factory(2)->create();
        $user0 = $users[0];
        $user1 = $users[1];

        $updateData = [
            "name" => "Updatedname",
            "firstname" => "Updatedfirstname",
            "email" => $user1->email,
        ];

        $response = $this->put(self::BASE_ROUTE . $user0->id, $updateData);

        $this->assertUserUpdate(
            $response,
            400,
            "existed_email",
            $user0->toArray()
        );
    }

    /**
     * should delete a user
     *
     * @return void
     */
    public function testShouldDeleteUser()
    {
        $user = User::factory()->create();
        $response = $this->delete(self::BASE_ROUTE . $user->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing("users", ["id" => $user->id]);
    }

    /**
     * should fail deleting a user when given invalid id
     *
     * @return void
     */
    public function testShouldFailDeleteUserWhenInvalidId()
    {
        $user = User::factory()->create();
        $response = $this->delete(self::BASE_ROUTE . "10");
        $response->assertStatus(404);
        $this->assertDatabaseHas("users", ["id" => $user->id]);
    }
}
