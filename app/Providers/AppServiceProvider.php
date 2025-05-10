<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
    public function boot(): void
    {
        Model::unguard();

        FilamentAsset::register([
            Css::make('fonts-css', Vite::useHotFile('fonts.hot')->asset('resources/css/fonts.css', 'build')),
            Css::make('app-css', Vite::useHotFile('app.hot')->asset('resources/css/app.css', 'build')),
            Js::make('app-js', Vite::useHotFile('app.hot')->asset('resources/js/app.js', 'build')),
        ]);
    }
}
