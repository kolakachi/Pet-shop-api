<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
    public function itCanGenerateAToken()
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);

        $this->assertNotNull($token);
        $this->assertIsString($token->toString());
    }

    /** @test */
    public function itCanParseAToken()
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        $parsedToken = $this->jwtService->parseToken($token->toString());

        $this->assertEquals($user->uuid, $parsedToken->claims()->get('user_uuid'));
    }
}
