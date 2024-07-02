<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
    }

    /** @test */
    public function it_can_create_an_admin()
    {
        $token = $this->getToken();
        $userData = $this->getUserData();
        $response = $this->postJson('/api/v1/admin/create', $userData,
            ['Authorization' => "Bearer {$token}"]
        );
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ]);
    }

    private function getUserData(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_admin' => true,
            'email' => 'user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => 'New Road',
            'phone_number' => '+23490997465',
            'is_marketing' => true,
        ];
    }

    protected function getToken(): string
    {
        $user = User::factory()->create(['is_admin' => true]);

        $token = $this->jwtService->generateToken($user);

        return $token->toString();
    }
}
