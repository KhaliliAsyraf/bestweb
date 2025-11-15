<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Http\Requests\Product\ProductAllRequest;
use App\Http\Requests\Product\ProductDeleteBulkRequest;
use App\Http\Requests\Product\ProductDeleteRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Interfaces\ServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ServiceInterface $productService
    )
    {
        // 
    }

    /**
     * To store new product
     * 
     * @param ProductStoreRequest $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/product",
     *     summary="Create a new product",
     *     description="Add a new product to the system.",
     *     tags={"Product"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","category_id","description","price","stock","enabled"},
     *             @OA\Property(property="name", type="string", example="Nasi Lemak"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="blabla"),
     *             @OA\Property(property="price", type="number", format="float", example=4.50),
     *             @OA\Property(property="stock", type="integer", example=2),
     *             @OA\Property(property="enabled", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="name", type="string", example="Nasi Lemak"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="blabla"),
     *                 @OA\Property(property="price", type="number", format="float", example=4.50),
     *                 @OA\Property(property="stock", type="integer", example=2),
     *                 @OA\Property(property="enabled", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-12T10:23:45Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The selected category id is invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected category id is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
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
     *
     * @OA\Get(
     *     path="/api/product",
     *     summary="Retrieve list of products",
     *     description="Fetch a paginated list of products. Optionally filter by category name.",
     *     tags={"Product"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter products by category name",
     *         required=false,
     *         @OA\Schema(type="string", example="food")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Retrieved products success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Retrieved products success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Nasi Lemak"),
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(property="description", type="string", example="blabla"),
     *                         @OA\Property(property="price", type="number", format="float", example=4.5),
     *                         @OA\Property(property="stock", type="integer", example=2),
     *                         @OA\Property(property="enabled", type="boolean", example=true),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-12T14:27:47.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-12T14:27:47.000000Z"),
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="food")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="path", type="string", example="http://localhost:8085/api/product"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="next_cursor", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="prev_cursor", type="string", nullable=true, example=null),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The selected category is invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected category is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", example="Retrieve products encounter error.")
     *         )
     *     )
     * )
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
     *
     * @OA\Get(
     *     path="/api/product/{id}",
     *     summary="Retrieve specific product by ID",
     *     description="Fetch a single product record including its category details.",
     *     tags={"Product"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Retrieved product success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Retrieved product success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Nasi Lemak"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="blabla"),
     *                 @OA\Property(property="price", type="number", format="float", example=4.5),
     *                 @OA\Property(property="stock", type="integer", example=2),
     *                 @OA\Property(property="enabled", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-12T14:27:47.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-12T14:27:47.000000Z"),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="food")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (invalid ID)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The selected id is invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected id is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Something when wrong")
     *         )
     *     )
     * )
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
     *
     * @OA\Put(
     *     path="/api/product/{id}",
     *     operationId="updateProduct",
     *     tags={"Product"},
     *     summary="Update a specific product",
     *     description="Update an existing product by its ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "category_id", "description", "price", "stock", "enabled"},
     *             @OA\Property(property="name", type="string", example="Nasi Lemak"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="blabla"),
     *             @OA\Property(property="price", type="number", format="float", example=4.50),
     *             @OA\Property(property="stock", type="integer", example=2),
     *             @OA\Property(property="enabled", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Nasi Lemaks"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="blabla"),
     *                 @OA\Property(property="price", type="number", format="float", example=4.5),
     *                 @OA\Property(property="stock", type="integer", example=2),
     *                 @OA\Property(property="enabled", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-12T14:27:47.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-12T14:38:13.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The selected id is invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id", type="array",
     *                     @OA\Items(type="string", example="The selected id is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something when wrong")
     *         )
     *     )
     * )
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
     *
     * @OA\Delete(
     *     path="/api/product/{id}",
     *     operationId="deleteProduct",
     *     tags={"Product"},
     *     summary="Delete a specific product",
     *     description="Delete an existing product by its ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The selected id is invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="id", type="array",
     *                     @OA\Items(type="string", example="The selected id is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something when wrong")
     *         )
     *     )
     * )
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
     *
     * @OA\Post(
     *     path="/api/product/delete-bulk",
     *     operationId="bulkDeleteProduct",
     *     tags={"Product"},
     *     summary="Bulk delete products",
     *     description="Delete multiple products by their IDs",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 example={1, 2}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product bulk deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product bulk deleted successfully."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The selected ids.1 is invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="ids.1", type="array",
     *                     @OA\Items(type="string", example="The selected ids.1 is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something when wrong")
     *         )
     *     )
     * )
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
