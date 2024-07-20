<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
   public function register()
    {
        $this->app->singleton('firebase', function ($app) {
            $config = $app['config']['firebase'];
            return (new Factory)
                ->withServiceAccount($config['credentials'])
                ->create();
        });
    }
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
