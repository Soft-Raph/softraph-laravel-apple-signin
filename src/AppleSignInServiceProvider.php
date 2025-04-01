<?php

namespace Softraph\LaravelAppleSignin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppleSignInServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        $this->publishes([
            __DIR__.'/../config/applesignin.php' => config_path('applesignin.php'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/../database/migrations/2025_04_01_000000_create_social_accounts_table.php'
            => database_path('migrations/' . date('Y_m_d_His') . '_create_social_accounts_table.php'),
        ], 'migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/applesignin.php', 'applesignin');
    }
}
