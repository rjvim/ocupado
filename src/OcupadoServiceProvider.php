<?php

namespace Betalectic\Ocupado;

use Illuminate\Support\ServiceProvider;
use Betalectic\Ocupado\Helpers;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class OcupadoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->setUpConfig();

        // class_alias(Helpers::getDynamicController(), 'Betalectic\Ocupado\Http\Controllers\DynamicController');
    }

    protected function setUpConfig()
    {
        $source = dirname(__DIR__) . '/config/ocupado.php';

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('ocupado.php')], 'config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('ocupado');
        }

        $this->mergeConfigFrom($source, 'ocupado');
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
