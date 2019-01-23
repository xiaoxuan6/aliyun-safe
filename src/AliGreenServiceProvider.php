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
        $this->publishes([
            __DIR__ . '/config/aliyun.php' => config_path('aliyun.php') // 发布配置文件到 laravel 的config 下
        ], 'config');
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
