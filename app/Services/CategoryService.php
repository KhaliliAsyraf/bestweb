<?php 

namespace App\Services;

use App\Interfaces\ServiceInterface;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService implements ServiceInterface
{
    /**
     * To store and return new or existing product
     * 
     * @param array $data
     * @return Product
     */
    public function getAll(): Collection
    {
        // 1 day
        return Cache::remember('products', 60 * 60 * 24, function () {
            return Category::get();
        });
    }
}