<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create default users before each test
        $this->user = User::factory()->create();
        // $this->seed();
    }

    /**
     * store_product_success
     */
    public function test_store_product_success(): void
    {
        $product = Product::factory()->make()->toArray();
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.product.store'), $product)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $product['name']
                ]
            );
        $this->assertDatabaseHas('products', ['name' => $product['name']]);
    }

    /**
     * store_product_validation_failed
     */
    public function test_store_product_validation_failed(): void
    {
        $product = Product::factory()->make()->toArray();
        $product['category_id'] = -1;
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.product.store'), $product)
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    'The selected category id is invalid.'
                ]
            );
        $this->assertDatabaseMissing('products', ['name' => $product['name']]);
    }
}
