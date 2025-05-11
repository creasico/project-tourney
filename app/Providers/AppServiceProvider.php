<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\ViteManifestNotFoundException;
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

        try {
            FilamentAsset::register([
                Css::make('app-css', Vite::asset('resources/css/app.css')),
                Js::make('app-js', Vite::asset('resources/js/app.js'))->module(),
            ]);
        } catch (ViteManifestNotFoundException $err) {
            // Prevent any error while running in CI
            // See https://github.com/creasico/project-tourney/pull/9
        }
    }
}
