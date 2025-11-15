<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::name('api.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::prefix('product')
            ->name('product.')
            ->group(function () {
                Route::post('/', [ProductController::class, 'store'])->name('store');
                Route::get('/', [ProductController::class, 'index'])->name('index');
                Route::get('/{id}', [ProductController::class, 'get'])->name('get');
                // Route::delete('/{id}', [ProductController::class, 'delete'])->name('delete');
                Route::put('/{id}', [ProductController::class, 'update'])->name('update');
                Route::delete('/{id}', [ProductController::class, 'delete'])->name('delete');
                Route::post('/delete-bulk', [ProductController::class, 'deleteBulk'])->name('delete-bulk');
                Route::get('/download/report', [ProductController::class, 'exportExcel'])
                    ->middleware(['throttle:download'])
                    ->name('export-excel');
            });
        
        Route::prefix('category')
            ->name('category.')
            ->group(function () {
                Route::get('/', [CategoryController::class, 'index'])->name('index');
            });
    });

