<?php

namespace GoodyTech\SyncBatch\Provider;

use GoodyTech\LaravelLogger\WebRequestLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppProvider extends ServiceProvider {


    function boot() {
        //マイグレーションファイルを読み込ませる
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        //設定ファイルのコピー
        $this->publishes([__DIR__.'/../config/syncbatch.php'=>config_path('syncbatch.php')]);
    }

    function register(): void {


    }

}