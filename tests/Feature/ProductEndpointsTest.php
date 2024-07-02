<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
    }

    /** @test */
    public function it_can_list_products()
    {
        Product::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data.products.data');
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $categoryUuid = Category::factory()->create()->uuid;
        $data = [
            'category_uuid' => $categoryUuid,
            'title' => 'Test Product',
            'price' => 99.99,
            'description' => 'This is a test product',
            'metadata' => json_encode(['brand' => '123e4567-e89b-12d3-a456-426614174002', 'image' => '123e4567-e89b-12d3-a456-426614174003']),
        ];

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $this->jwtService->generateToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer '.$token->toString())
            ->postJson('/api/v1/product/create', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'category_uuid',
                    'uuid', 'title',
                    'price', 'description',
                    'metadata',
                ],
            ]);

        $this->assertDatabaseHas('products', ['title' => 'Test Product']);
    }

    /** @test */
    public function it_can_get_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/product/{$product->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => $product->title,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $product = Product::factory()->create();

        $data = [
            'title' => 'Updated Product Title',
            'price' => 199.99,
            'description' => 'Updated product description',
            'metadata' => json_encode(['brand' => '123e4567-e89b-12d3-a456-426614174002', 'image' => '123e4567-e89b-12d3-a456-426614174003']),
        ];

        $admin = User::factory()->create(['is_admin' => true]);
        $token = $this->jwtService->generateToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer '.$token->toString())
            ->putJson("/api/v1/product/{$product->uuid}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Updated Product Title',
            ]);

        $this->assertDatabaseHas('products', ['title' => 'Updated Product Title']);
    }
}
