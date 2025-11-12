<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Http\Requests\Product\ProductAllRequest;
use App\Http\Requests\Product\ProductDeleteBulkRequest;
use App\Http\Requests\Product\ProductDeleteRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Product;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    )
    {
        // 
    }

    /**
     * To store new product
     * 
     * @param ProductStoreRequest $request
     * @return JsonResponse
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $lockKey = "store-product:" . auth()->user()->id;
        $lock = Cache::lock($lockKey, 5); // lock expires after 5 seconds

        if ($lock->get()) {
            try {
                DB::beginTransaction();

                $product = $this->productService->store($request->validated());

                DB::commit();

                return response()->json(
                    [
                        'message' => 'Product stored successfully.',
                        'data' => $product->toArray()
                    ],
                    200
                );

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('(Product) store error: ' . $e->getMessage());
                return response()->json(
                    [
                        'error' => 'Store product encounter error.'
                    ],
                    500
                );
            } finally {
                $lock->release(); // ensure lock is released
            }
        }

        // If cannot acquire lock — another request is processing
        return response()->json(['error' => 'Please wait, your request is being processed.'], 429);
    }

    /**
     * To return all products
     * 
     * @param ProductAllRequest $request
     * @return JsonResponse
     */
    public function index(ProductAllRequest $request): JsonResponse
    {
        try {
            return response()->json(
                [
                    'message' => 'Retrieved products success',
                    'data' => $this->productService->getAll($request->category, true)
                ]
            );
        } catch (Exception $e) {
            Log::error('(Product) retrieve all error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Retrieve products encounter error.'
                ],
                500
            );
        }
    }

    /**
     * To return specified product
     * 
     * @param ProductRequest $request
     * @return JsonResponse
     */
    public function get(ProductRequest $request): JsonResponse
    {
        try {
            return response()->json(
                [
                    'message' => 'Retrieved product success',
                    'data' => $this->productService->get($request->id)
                ]
            );
        } catch (Exception $e) {
            Log::error('(Product) retrieve one error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Retrieve product encounter error.'
                ],
                500
            );
        }
    }

    /**
     * To update a product
     * 
     * @param ProductUpdateRequest $request
     * @return JsonResponse
     */
    public function update(ProductUpdateRequest $request): JsonResponse
    {
        $lockKey = "store-product:" . auth()->user()->id;
        $lock = Cache::lock($lockKey, 5); // lock expires after 5 seconds

        if ($lock->get()) {
            try {
                DB::beginTransaction();

                $product = $this->productService->update($request->validated());

                DB::commit();

                return response()->json(
                    [
                        'message' => 'Product updated successfully.',
                        'data' => $product->toArray()
                    ],
                    200
                );

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('(Product) update error: ' . $e->getMessage());
                return response()->json(
                    [
                        'error' => 'Update product encounter error.'
                    ],
                    500
                );
            } finally {
                $lock->release();
            }
        }
        
        return response()->json(['error' => 'Please wait, your request is being processed.'], 429);
    }

    /**
     * To delete a product
     * 
     * @param ProductDeleteRequest $request
     * @return JsonResponse
     */
    public function delete(ProductDeleteRequest $request): JsonResponse
    {
        try {
            $this->productService->delete($request->id);

            return response()->json(
                    [
                        'message' => 'Product deleted successfully.',
                        'data' => null
                    ],
                    200
                );
        } catch (Exception $e) {
            Log::error('(Product) delete error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Delete product encounter error.'
                ],
                500
            );
        }
    }

    /**
     * To delete bulk of product
     * 
     * @param ProductDeleteBulkRequest $request
     * @return JsonResponse
     */
    public function deleteBulk(ProductDeleteBulkRequest $request): JsonResponse
    {
        try {
            $this->productService->deleteBulk($request->ids);

            return response()->json(
                    [
                        'message' => 'Product bulk deleted successfully.',
                        'data' => null
                    ],
                    200
                );
        } catch (Exception $e) {
            Log::error('(Product) delete bulk error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Delete bulk of product encounter error.'
                ],
                500
            );
        }
    }

    /**
     * To export products to excel
     * 
     * @return JsonResponse|BinaryFileResponse
     */
    public function exportExcel(): JsonResponse|BinaryFileResponse
    {
        try {
            $fileName = 'products_' . now()->format('Ymd_His') . '.csv';
            return Excel::download(new ProductsExport, $fileName, \Maatwebsite\Excel\Excel::CSV);
        } catch (Exception $e) {dd($e->getMessage());
            Log::error('(Product) export excel error: ' . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Export product excel encounter error.'
                ],
                500
            );
        }
    }
}
