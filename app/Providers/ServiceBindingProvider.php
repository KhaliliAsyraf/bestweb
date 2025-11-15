<?php

namespace App\Providers;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Interfaces\ServiceInterface;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

class ServiceBindingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->when(ProductController::class)
            ->needs(ServiceInterface::class)
            ->give(ProductService::class);

        $this->app->when(CategoryController::class)
            ->needs(ServiceInterface::class)
            ->give(CategoryService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
