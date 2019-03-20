<?php

namespace Betalectic\FileManager;

use Illuminate\Support\ServiceProvider;
use Betalectic\FileManager\Helpers;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes.php');

        $this->setUpConfig();

        class_alias(Helpers::getDynamicController(), 'Betalectic\FileManager\Http\Controllers\DynamicController');
    }

    protected function setUpConfig()
    {
        $source = dirname(__DIR__) . '/config/file-manager.php';

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('file-manager.php')], 'config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('file-manager');
        }

        $this->mergeConfigFrom($source, 'file-manager');

        \Cloudinary::config([
          'cloud_name' => config('file-manager.cloudinary_cloud_name'),
          'api_key' => config('file-manager.cloudinary_api_key'),
          'api_secret' => config('file-manager.cloudinary_api_secret')
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
