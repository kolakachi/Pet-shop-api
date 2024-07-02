<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\JwtService;
use Tests\TestCase;

class CategoryEndpointsTest extends TestCase
{
    protected $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
    }

    /** @test */
    public function admin_can_list_categories()
    {
        $token = $this->getToken();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/categories');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_get_category()
    {
        $token = $this->getToken();
        $category = Category::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/category/'.$category->uuid);
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_category()
    {
        $data = [
            'title' => 'New Category',
            'slug' => 'new-category',
        ];

        $token = $this->getToken();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/category/create', $data);
        $response->assertStatus(200);
    }

    protected function getToken(): string
    {
        $user = User::factory()->create(['is_admin' => true]);

        $token = $this->jwtService->generateToken($user);

        return $token->toString();
    }
}
