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

    /** @test */
    public function it_can_login_as_admin()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token'],
            ]);
    }

    /** @test */
    public function it_can_logout_as_admin()
    {
        $token = $this->getToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/logout');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'message' => 'Logged out successfully',
                ],
            ]);
    }

    /** @test */
    public function it_can_list_all_non_admin_users()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(10)->create(['is_admin' => false]);
        $token = $this->jwtService->generateToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer '.$token->toString())
            ->getJson('/api/v1/admin/user-listing');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function it_can_edit_a_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $token = $this->jwtService->generateToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer '.$token->toString())
            ->putJson('/api/v1/admin/user-edit/'.$user->uuid, [
                'first_name' => 'UpdatedName',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'first_name' => 'UpdatedName',
        ]);
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $token = $this->jwtService->generateToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer '.$token->toString())
            ->deleteJson('/api/v1/admin/user-delete/'.$user->uuid);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'message' => 'User deleted successfully',
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
