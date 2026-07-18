<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // reconnecting before hitting network cap for free remote db server
        $reconnectEvery = (int) env('DB_RECONNECT_EVERY', 0);

        if ($reconnectEvery > 0) {
            $executed = 0;

            DB::listen(function ($query) use (&$executed, $reconnectEvery) {
                if (++$executed >= $reconnectEvery
                    && $query->connection->transactionLevel() === 0) {
                    $executed = 0;
                    $query->connection->reconnect();
                }
            });
        }
    }
}
