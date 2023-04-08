<?php
/**
 * @author Alex Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Alex Milenin (https://azrr.info/)
 */

namespace App\Component;

use App\Config;
use App\Model\Data\Gateway;
use Azurre\Component\System\Shell;
use Exception;
use Psr\Log\LoggerAwareTrait;

class WpaManager
{
    use LoggerAwareTrait;

    protected Config $config;
    protected string $wpaBin = 'wpa_supplicant';
    protected string $dhcpBin = 'dhclient';
    protected string $ipBin = 'ip';
    protected string $killBin = 'killall';

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function checkConnection(): bool
    {
        $pingCount = $this->config->pingCount->val();
        $pingLossThreshold = $this->config->pingLossThreshold->val();
        $successCount = (new Ping($this->config))->execute();
        $lossCount = $pingCount - $successCount;
        $this->logger->debug("Check connection: $successCount of $pingCount, limit $pingLossThreshold");
        return $lossCount <= $pingLossThreshold;
    }

    /**
     * @throws Exception
     */
    public function connect(string $essid): void
    {
        $this->logger->debug("Connecting to $essid");
        $iface = $this->config->interface->val();
        $essidList = $this->config->essidList->val();
        $wpaConfigPath = $essidList[$essid] ?? null;
        $this->logger->debug("Connect: $iface, config path $wpaConfigPath");
        if (!$wpaConfigPath) {
            throw new Exception("Cannot find $essid config");
        }

        Shell::create()->run("$this->killBin $this->wpaBin");
        $cmd = "$this->wpaBin -B -i $iface -c$wpaConfigPath"; //  > /dev/null 2>&1
        Shell::create()->run($cmd);
        Shell::create()->run("$this->dhcpBin -r $iface");
        Shell::create()->run("$this->dhcpBin $iface"); //   > /dev/null 2>&1
    }

    public function enableInterface(): void
    {
        $iface = $this->config->interface->val();
        Shell::create()->run("$this->ipBin link set $iface up");
    }

    protected function getGateway(): Gateway|false
    {
        $res = false;
        $out = Shell::create()->run("$this->ipBin r")->getStdOut();
        if (preg_match('/default\svia\s([\d.]+)\sdev\s([a-z\d]+)/i', $out, $matches)) {
            $res = new Gateway($matches[1], $matches[2]);
        }

        return $res;
    }

    /**
     * @param string|null $ip
     * @param string|null $iface
     * @return $this
     * @throws Exception
     */
    protected function setDefaultGateway(string $ip = null, string $iface = null): static
    {
        $shell = Shell::create()->run("$this->ipBin r a default" . ($ip ?: " via $ip"). ($iface ?: " dev $iface" ));
        $this->handleShellError($shell);

        return $this;
    }

    /**
     * @param Gateway $gateway1
     * @param Gateway $gateway2
     * @return $this
     * @throws Exception
     */
    protected function setGatewayPriority(Gateway $gateway1, Gateway $gateway2): static
    {
        $cmd = 'ip r a default scope global';
        $cmd .= " nexthop via $gateway1->ip dev $gateway1->iface weight $gateway1->priority";
        $cmd .= " nexthop via $gateway2->ip dev $gateway2->iface weight $gateway2->priority";

        $shell = Shell::create()->run($cmd);
        $this->handleShellError($shell);

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function removeGateway(): static
    {
        $shell = Shell::create()->run("$this->ipBin r d default");
        $this->handleShellError($shell);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function handleGateway(): static
    {
        $wlanIface = $this->config->interface->val();
        $gatewayMode = strtolower((string)$this->config->gatewayMode->val());
        $gateway = $this->getGateway();
        switch ($gatewayMode) {
            case Gateway::MODE_WLAN:
                if ($gateway && $gateway->iface !== $wlanIface) {
                    $this->removeGateway();
                    $this->setDefaultGateway($gateway->ip, $this->config->interface->val());
                }
                break;
            case Gateway::MODE_BALANCED:
            case Gateway::MODE_ETH_PRIORITY:
            case Gateway::MODE_WLAN_PRIORITY:
                if ($gateway && $gateway->iface !== $wlanIface) {
                    $this->removeGateway();
                    [$ethPriority, $wlanPriority] = match ($gatewayMode) {
                        Gateway::MODE_BALANCED => [100,100],
                        Gateway::MODE_ETH_PRIORITY => [100, 99],
                        Gateway::MODE_WLAN_PRIORITY => [99, 100]
                    };
                    $ethGateway = new Gateway($gateway->ip, $gateway->iface, $ethPriority);
                    $wlanGateway = new Gateway($gateway->ip, $wlanIface, $wlanPriority);
                    $this->setGatewayPriority($ethGateway, $wlanGateway);
                }
                break;

            case Gateway::MODE_DEFAULT:
                // Do nothing
                break;
        }
        return $this;
    }

    public function testBin(): bool
    {
        return !Shell::create()->run("$this->wpaBin -h")->getExitCode();
    }

    public function testDhcp(): bool
    {
        return !Shell::create()->run("$this->dhcpBin  -h")->getExitCode();
    }

    public function testIp(): bool
    {
        return !Shell::create()->run("$this->ipBin r")->getExitCode();
    }

    public function testKill(): bool
    {
        return !Shell::create()->run("$this->killBin -l")->getExitCode();
    }

    /**
     * @param Shell $shell
     * @return void
     * @throws Exception
     */
    protected function handleShellError(Shell $shell): void
    {
        if ($shell->getExitCode()) {
            throw new Exception("{$shell->getCwd()}: {$shell->getStdError()}");
        }
    }
}
