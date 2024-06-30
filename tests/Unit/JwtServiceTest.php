<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\JwtService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JwtServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = $this->app->make(JwtService::class);
    }

    /** @test */
    public function it_can_generate_a_token()
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);

        $this->assertNotNull($token);
        $this->assertIsString($token->toString());
    }

    /** @test */
    public function it_can_parse_a_token()
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        $parsedToken = $this->jwtService->parseToken($token->toString());

        $this->assertEquals($user->uuid, $parsedToken->claims()->get('user_uuid'));
    }
}
