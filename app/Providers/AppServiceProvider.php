<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Services\DeliberationPacesService;
use App\Services\FusionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    //    $this->app->singleton(DeliberationPacesService::class);
    //    $this->app->singleton(FusionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        if (app()->environment('production')) {
            ini_set('max_execution_time', env('MAX_EXECUTION_TIME', 300));
            ini_set('memory_limit', env('MEMORY_LIMIT', '512M'));
        }
    }
}
