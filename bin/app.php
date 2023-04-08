<?php

use App\Component\Message;
use App\Component\Wlan;
use App\Config;

[$config] = include __DIR__ . '/../bootstrap.php';

class App
{
    const DEFAULT_CONFIG_PATH = APP_DIR . '/config.json';
    const OPTION_CONFIG_PATH = 'c';
    const OPTION_HELP = 'h';
    const OPTION_DRY_RUN = 'dry-run';

    public function __construct(Config $config)
    {
        $shortOptions = implode('::', [static::OPTION_HELP . static::OPTION_CONFIG_PATH, '']);
        $options = (array)getopt($shortOptions, [static::OPTION_DRY_RUN]);

        if (isset($options[static::OPTION_HELP])) {
            return $this->help();
        }

        $configPath = $options[static::OPTION_CONFIG_PATH] ?? static::DEFAULT_CONFIG_PATH;
        $this->loadConfig($config, $configPath);

        if (isset($options[static::OPTION_DRY_RUN])) {
            $action = Wlan::ACTION_DRY_RUN;
        } else {
            $action = Wlan::ACTION_CHECK_CONNECTION;
        }

        $wlan = new Wlan($config);
        $wlan->run($action);
        return $this;
    }

    protected function help(): static
    {
        echo "\nWiFi Connection Manager\n\n";
        echo Message::warning('Usage:') . "\n";
        echo " php command [options] \n\n";
        echo Message::warning('Options:') . "\n";
        echo Message::success('  -c ') . "         Path to JSON config\n";
        echo Message::success('  -h ') . "         Show help information\n";
        echo Message::success('  --dry-run ') . "  Check all the requirements\n";
        echo Message::warning('Example:') . "\n";
        echo '  php ' . basename(APP_PATH) . " --dry-run\n\n";

        return $this;
    }

    protected function loadConfig(Config $config, string $path): static
    {
        if (is_file($path) && is_readable($path)) {
            $content = file_get_contents($path);
            if ($content) {
                try {
                    $localConfig = json_decode($content, true, 10, JSON_THROW_ON_ERROR);
                } catch (\Exception $e) {
                    exit("Cannot parse config $path: {$e->getMessage()}\n");
                }
                $config->merge($localConfig);
            }
        } else {
            if ($path !== static::DEFAULT_CONFIG_PATH) {
                exit("Cannot read config '$path'\n");
            }
        }
        return $this;
    }
}

new App($config);
