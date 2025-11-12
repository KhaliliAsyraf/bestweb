<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'category_id' => Category::factory()->create()->id,
            'description' => fake()->text(75),
            'price' => fake()->randomFloat(1, 20, 30),
            'stock' => fake()->randomDigit(),
            'enabled' => true
        ];
    }
}
