<?php

namespace Wovosoft\LaravelMessenger;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/laravel-messenger.php';

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => config_path('laravel-messenger.php'),
            ], 'config');
        }


        $this->loadMigrationsFrom(__DIR__ . "/../database/migrations");
        $this->loadRoutesFrom(__DIR__ . "/routes.php");
        $this->loadFactoriesFrom(__DIR__ . "/../database/factories");
        $this->loadViewsFrom(__DIR__ . "/../resources/views", 'LaravelMessenger');
        $this->loadTranslationsFrom(__DIR__ . "/../resources/lang", 'LaravelMessenger');
        //$this->loadJsonTranslationsFrom(__DIR__."/../resources/lang");
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'laravel-messenger'
        );

        $this->app->bind('LaravelMessenger', LaravelMessenger::class);
    }
}
