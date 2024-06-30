<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;

class UserEndpointsTest extends TestCase
{
    use RefreshDatabase;
    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = app(JwtService::class);
    }

    /** @test */
    public function it_creates_a_new_user()
    {
        $userData = $this->getUserData();
        $userData["password_confirmation"] = $userData["password"];
        $response = $this->postJson("/api/v1/user/create", $userData);

        $response->assertStatus(200)
            ->assertJson([
                "success" => 1,
            ])->assertJsonStructure([
                "data" => [
                    "uuid",
                    "email",
                    "first_name",
                    "last_name",
                    "token"
                ],
                "error",
                "errors",
                "extra",
            ]);

        $this->assertDatabaseHas("users", [
            "email" => "user@example.com",
        ]);
    }

    /** @test */
    public function it_logs_in_a_user()
    {
        $user = $this->getUser();

        $loginData = [
            "email" => "user@example.com",
            "password" => "userpassword",
        ];

        $response = $this->postJson("/api/v1/user/login", $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    "success",
                    "data" => [
                        "token"
                    ],
                    "error",
                    "errors",
                    "extra",
                ]);
    }

    /** @test */
    public function it_logs_out_a_user()
    {
        $user = $this->getUser();

        $token = $this->authenticate($user);

        $response = $this->getJson("/api/v1/user/logout", ["Authorization" => "Bearer {$token}"]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    "success",
                    "data",
                    "error",
                    "errors",
                    "extra",
                ]);
    }

    /** @test */
    public function it_returns_the_logged_in_user()
    {
        $user = $this->getUser();

        $token = $this->authenticate($user);
        $response = $this->getJson("/api/v1/user", ["Authorization" => "Bearer {$token}"]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "success",
                "data" => [
                    "uuid",
                    "email"
                ],
                "error",
                "errors",
                "extra",
            ]);
    }

    /** @test */
    public function it_deletes_the_logged_in_user()
    {
        $user = $this->getUser();

        $token = $this->authenticate($user);

        $response = $this->deleteJson("/api/v1/user", [], ["Authorization" => "Bearer {$token}"]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "success",
                "data",
                "error",
                "errors",
                "extra",
            ]);

        $this->assertDatabaseMissing("users", [
            "email" => "user@example.com",
        ]);
    }

    /** @test */
    public function it_edits_user_details()
    {
        $user = $this->getUser();

        $token = $this->authenticate($user);

        $editUser = $user->toArray();

        $editUser["first_name"] = "Johnny";
        $editUser["last_name"] = "Cash";
        $editUser["password"] = Hash::make("userpasswordchange");
        $editUser["password_confirmation"] = $editUser["password"];
        $editUser["email"] = "john.cash@example.com";

        $response = $this->putJson("/api/v1/user/edit", $editUser, ["Authorization" => "Bearer {$token}"]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    "success",
                    "data",
                    "error",
                    "errors",
                    "extra",
                ]);

        $this->assertDatabaseHas("users", [
            "email" => "john.cash@example.com",
            "first_name" => "Johnny",
            "last_name" => "Cash",
        ]);
    }

    protected function authenticate(User $user): string
    {
        $token = $this->jwtService->generateToken($user);
        return $token->toString();
    }

    private function getUser(): User
    {
        $user = User::factory()->create($this->getUserData());

        return $user;
    }

    private function getUserData(): array
    {
        return [
            "uuid" => Str::uuid()->toString(),
            "first_name" => "John",
            "last_name" => "Doe",
            "is_admin" => false,
            "email" => "user@example.com",
            "password" => Hash::make("userpassword"),
            "address" => "New Road",
            "phone_number" => "+23490997465",
            "is_marketing" => true,
        ];
    }
}
