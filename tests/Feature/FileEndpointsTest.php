<?php

namespace Tests\Feature;

use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = app(JwtService::class);
    }
}
