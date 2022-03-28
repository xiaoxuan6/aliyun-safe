<?php
/**
 * This file is part of PHP CS Fixer.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
spl_autoload_register('Autoloader::autoload');
class Autoloader
{
    private static $autoloadPathArray = [
        'aliyun-php-sdk-core',
        'aliyun-php-sdk-core/Auth',
        'aliyun-php-sdk-core/Http',
        'aliyun-php-sdk-core/Profile',
        'aliyun-php-sdk-core/Regions',
        'aliyun-php-sdk-core/Exception'
    ];

    public static function autoload($className)
    {
        foreach (self::$autoloadPathArray as $path) {
            $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $className . '.php';
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            if (is_file($file)) {
                include_once $file;

                break;
            }
        }
    }

    public static function addAutoloadPath($path)
    {
        array_push(self::$autoloadPathArray, $path);
    }
}
