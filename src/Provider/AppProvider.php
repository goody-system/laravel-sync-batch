<?php

namespace GoodyTech\SyncBatch\Provider;

use GoodyTech\LaravelLogger\WebRequestLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppProvider extends ServiceProvider {


    function boot() {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    function register(): void {


    }

}