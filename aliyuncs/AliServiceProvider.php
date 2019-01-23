<?php

namespace aliyuncs;

use Illuminate\Support\ServiceProvider;

class AliServiceProvider extends ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__ . '/config/aliyun.php';
        $this->mergeConfigFrom($configPath,"aliyun");
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/config/aliyun.php' => config_path("aliyun.php")], 'aliyun');
    }

}