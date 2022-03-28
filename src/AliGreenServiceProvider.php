<?php
/**
 * This file is part of PHP CS Fixer.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace James\AliGreen;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

class AliGreenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/config/aliyun.php' => config_path('aliyun.php')], 'aliyun');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('aliyun');
        }

        $this->mergeConfigFrom(__DIR__ . '/config/aliyun.php', 'aliyun');
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
