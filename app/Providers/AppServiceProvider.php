<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

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
     *
     * @return void
     */
    public function boot()
    {
        error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
//        if(env('APP_DEBUG')) {
//            DB::listen(function($query) {
//                File::append(
//                    storage_path('/logs/query.log'),
//                    $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
//                );
//            });
//        }
    }
}
