<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton('firebase', function ($app) {
            $config = $app['config']['services.firebase'];
            $serviceAccount = ServiceAccount::fromJsonFile($config['credentials']);

            return (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDatabaseUri($config['database_url'])
                ->create();
        });

        // Register the Firestore singleton
        $this->app->singleton('firebase.firestore', function ($app) {
            return $app->make('firebase')->firestore();
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
