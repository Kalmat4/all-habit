<?php

namespace App\Providers;

use DB;
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
        try {
            if (DB::connection()->getPdo()) {
                DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
            }
        } catch (\Throwable $e) {
            // можно залогировать, чтобы видеть причину, но не падать
        }
    }
}
