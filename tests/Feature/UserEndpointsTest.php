<?php

namespace Tests\Feature;

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
