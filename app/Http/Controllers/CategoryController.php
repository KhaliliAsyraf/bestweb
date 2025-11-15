<?php

namespace App\Http\Controllers;

use App\Interfaces\ServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function __construct(
        protected ServiceInterface $categoryService
    )
    {
        //
    }

    /**
     * To return all categories
     * 
     * @return JsonResponse
     * 
     * @OA\Get(
     *     path="/api/category",
     *     operationId="getCategories",
     *     tags={"Category"},
     *     summary="Retrieve all categories",
     *     description="Get a list of all categories",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="food"),
     *                     @OA\Property(property="created_at", type="string", example="2025-11-15T11:49:20.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-11-15T11:49:20.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something when wrong")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json(
                        [
                            'message' => 'Categories retrieved successfully.',
                            'data' => $this->categoryService->getAll()
                        ]
                    );
        } catch (Exception $e) {
            Log::error('(Category) retrieved product error: ' . $e->getMessage());
            return response()->json(
                    [
                        'error' => 'Retrieve categories encounter error.'
                    ],
                    500
                );
        }
        
    }
}
