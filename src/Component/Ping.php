<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App\Component;

use App\Config;
use Azurre\Component\System\Shell;
use Exception;

class Ping
{
    protected Config $config;
    protected string $bin = 'ping';

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->bin = $config->pingBin->val() ?? $this->bin;
    }

    /**
     * @return int Number of received responses
     * @throws Exception
     */
    public function execute(): int
    {
        $interval = $this->config->pingTimeout->val();
        $iface = $this->config->pingWlan->val() ? $this->config->interface->val() : null;
        $address = $this->config->pingAddress->val();
        $count = $this->config->pingCount->val();
        $ifaceCmd = $iface ? "-I $iface" : '';
        $intervalCmd = $interval ? "-i $interval" : '';
        $cmd = "$this->bin -c $count $intervalCmd $ifaceCmd $address";
        $shell = Shell::create()->run($cmd);
        return $this->parseResponse($shell->getStdOut());
    }

    public function testBin(): bool
    {
        return !Shell::create()->run("$this->bin -c 1 127.0.0.1")->getExitCode();
    }

    /**
     * @param string $response
     * @return int Number of received responses
     */
    protected function parseResponse(string $response): int
    {
        if (!preg_match('/(\d+) received/i', $response, $matches)) {
            return 0;
        }
        return (int)$matches[1];
    }
}
