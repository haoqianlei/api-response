<?php

namespace Back\ApiResponse;

use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__."/../config/response_builder.php" => config_path('response_builder.php')], 'api-response-config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        if (!$this->app->configurationIsCached()) {
            $defaults = require __DIR__.'/../config/response_builder.php';
            $this->app['config']->set('response_builder', \MarcinOrlowski\ResponseBuilder\Util::mergeConfig(
                $defaults,
                $this->app['config']->get('response_builder', [])
            ));
        }
        $this->app->bind('api-response', function () {
            return ApiResponse::asSuccess();
        });
    }
}
