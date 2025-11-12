<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Product API",
 *     version="1.0.0"
 * )
 * @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 */
abstract class Controller
{
    //
}
