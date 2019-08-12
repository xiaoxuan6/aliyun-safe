<?php

namespace James\AliGreen;

use Illuminate\Support\ServiceProvider;

class AliGreenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/config/aliyun.php' => config_path('aliyun.php') ], 'aliyun');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('aligreen', function ($app) {
            return new AliGreen();
        });
    }

}
