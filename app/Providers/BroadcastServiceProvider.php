<?php

namespace App\Providers;

use App\Events\MessageSent;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use App\Listeners\NewMessageListener;
use Illuminate\Support\Facades\Event;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
   public function boot()
   {
       Broadcast::routes(['middleware' => ['auth:sanctum']]);

       require base_path('routes/channels.php');
   }
 
}