<?php 

namespace App\Services;

use App\Exports\ProductsExport;
use App\Interfaces\ServiceInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductService implements ServiceInterface
{
    /**
     * To store and return new or existing product
     * 
     * @param array $data
     * @return Product
     */
    public function store(array $data, ?bool $toArray = null): Product
    {
        return Product::updateOrCreate($data);
    }

    /**
     * To return all filtered products
     * 
     * @return Collection|CursorPaginator
     */
    public function getAll(?string $category = null, bool $paginate = false): Collection|CursorPaginator
    {
        $products = Product::with(['category:id,name'])
            ->when(!empty($category), function ($productQuery) use ($category) {
                return $productQuery->whereHas('category', function ($categoryQuery) use ($category) {
                    return $categoryQuery->where('name', $category);
                });
            });
        return $paginate ? $products->cursorPaginate(10) : $products->get();
    }

    /**
     * To return specified product
     * 
     * @param int $id
     * @return Product
     */
    public function get(int $id): Product
    {
        return Product::with(['category:id,name'])->find($id);
    }

    /**
     * To store and return new or existing product
     * 
     * @param array $data
     * @return Product
     */
    public function update(array $data): Product
    {
        $product = Product::find($data['id']);
        $product->update(
            [
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'stock' => $data['stock'],
                'enabled' => $data['enabled']
            ]
        );
            
        return $product;
    }

    /**
     * To delete a product
     * 
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        Product::query()
            ->whereId($id)
            ->delete();
    }

    /**
     * To delete bulk of product
     * 
     * @param array $ids
     * @return void
     */
    public function deleteBulk(array $ids): void
    {
        Product::query()
            ->whereIn('id', $ids)
            ->delete();
    }
}