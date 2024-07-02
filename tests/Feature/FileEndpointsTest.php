<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    /** @test */
    public function it_can_upload_an_image()
    {

        $token = $this->getToken();
        Storage::fake('public/pet-shop');

        $response = $this->postJson('/api/v1/file/upload', [
            'file' => UploadedFile::fake()->image('test-image.jpg'),
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'uuid',
                'name',
                'path',
                'size',
                'type',
                'updated_at',
                'created_at',
            ],
            'error',
            'errors',
            'extra',
        ]);

        $this->assertDatabaseHas('files', [
            'name' => 'test-image.jpg',
        ]);

        Storage::assertExists(File::first()->path);
    }

    /** @test */
    public function it_can_download_an_image()
    {
        $token = $this->getToken();
        Storage::fake('pet-shop');

        $file = UploadedFile::fake()->image('test-image.jpg');
        $path = $file->store('pet-shop');
        $uuid = \Illuminate\Support\Str::uuid()->toString();

        File::create([
            'uuid' => $uuid,
            'name' => 'test-image.jpg',
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ]);

        $response = $this->getJson("/api/v1/file/{$uuid}", [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=test-image.jpg');
    }

    protected function getToken(): string
    {
        $user = User::factory()->create();

        $token = $this->jwtService->generateToken($user);

        return $token->toString();
    }
}
