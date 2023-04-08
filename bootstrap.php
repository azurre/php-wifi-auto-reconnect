<?php

use App\Config;

$file = $_SERVER['PHP_SELF'];
$dir = __DIR__;
if (str_starts_with(__DIR__, 'phar://')) {
    $file = substr(__DIR__, 7);
    $dir = dirname($file);
}
defined('APP_PATH') ||  define('APP_PATH', $file);
defined('APP_DIR') || define('APP_DIR', $dir);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);

function init()
{
    $configArray = [];
    $loader = include __DIR__ . '/vendor/autoload.php';
    try {
        $globalConfig = __DIR__ . '/config.php';
        $localConfig = __DIR__ . '/config.local.php';

        if (is_file($globalConfig) && is_readable($globalConfig)) {
            $configArray = include $globalConfig;
        }
        if (is_file($localConfig) && is_readable($localConfig)) {
            $configArray = array_merge($configArray, (array)include $localConfig);
        }
    } catch (Exception $e) {
        exit($e->getMessage() . PHP_EOL);
    }

    $config = new Config($configArray);

    if ($config->timezone->val()) {
        date_default_timezone_set($config->timezone->val());
    }

    return [$config, $loader];
}

return init();
