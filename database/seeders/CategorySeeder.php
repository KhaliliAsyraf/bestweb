<?php

namespace Database\Seeders;

use App\Models\Category;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $categories = [
                'food',
                'drink',
                'desert'
            ];

            Category::upsert(
                array_map(fn($category) => ['name' => $category], $categories),
                ['name'],
                [
                    'name'
                ]
            );
            
        } catch (Exception $e) {
            Log::error('Category seeder error: ', $e->getMessage());
        }
    }
}
