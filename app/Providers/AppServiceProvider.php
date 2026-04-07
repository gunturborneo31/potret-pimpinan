<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use App\Models\KegiatanFolder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    // public function boot(): void
    // {
    //     Vite::prefetch(concurrency: 3);
    // }

    public function boot()
{
    Inertia::share([
        'auth' => fn () => [
            'user' => auth()->user(),
        ],
    ]);

}

}
