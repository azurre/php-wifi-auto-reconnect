<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App\Component;

use App\Config;
use Azurre\Component\Logger;
use Azurre\Component\Logger\Handler\File;
use Exception;
use Psr\Log\LoggerInterface;

class Wlan
{
    public const ACTION_DRY_RUN = 0;
    public const ACTION_CHECK_CONNECTION = 1;

    protected Config $config;
    protected LoggerInterface $logger;

    public function __construct(Config $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $logFile = $config->logFile->val();
        $logLevel = $config->logLevel->val() ?? 'error';
        $logger = $logger ?? new Logger(null, $logLevel);
        $this->logger = $config->logger = $logger;
        if ($logFile) {
            if (is_file($logFile) && is_writeable($logFile) || (!is_file($logFile) && is_writeable(dirname($logFile)))) {
                $logger->setHandler(new File($logFile));
            }
        }
        if (!$config->interface->val()) {
            $wirelessTools = new WirelessTools($config);
            $list = $wirelessTools->getWlanList();
            $iface = reset($list);
            if ($iface) {
                $config->interface = $iface;
            }
        }
    }

    public function run(int $action = self::ACTION_CHECK_CONNECTION): self
    {
        try {
            switch ($action) {
                case self::ACTION_DRY_RUN:
                    return $this->dryRun();
                case self::ACTION_CHECK_CONNECTION:
                    return $this->checkConnection();
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $this;
    }

    /**
     * @return Wlan
     * @throws Exception
     */
    protected function checkConnection(): self
    {
        $wpaManager = new WpaManager($this->config);
        $wpaManager->setLogger($this->logger);
        $this->logger->debug('Checking connection...');
        if (!$wpaManager->checkConnection()) {
            $this->logger->debug('No connection or connection is unstable');
            $this->logger->debug('Trying to reconnect...');
            $wirelessTools = new WirelessTools($this->config);
            $wpaManager->enableInterface();
            $apList = $wirelessTools->getApList();
            $this->logger->debug(sizeof($apList) . ' APs found');
            $availableSsidList = $this->config->essidList->val();
            $essidList = array_keys($availableSsidList);
            $apList = array_filter($apList, fn($item) => in_array($item->essid, $essidList));
            $this->logger->debug(sizeof($apList) . ' APs available to connect');
            $ap = reset($apList);
            if ($ap) {
                $this->logger->debug("Selected AP: $ap->essid ($ap->signal)");
                $wpaManager->connect($ap->essid);
                if ($this->checkConnection()) {
                    $this->logger->info("Successfully connected to $ap->essid");
                    $wpaManager->handleGateway();
                } else {
                    $this->logger->error("Cannot connect to $ap->essid");
                }
            } else {
                $this->logger->error('Cannot connect. No AP available');
            }
        } else {
            $this->logger->debug('Connection is OK');
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function dryRun(): self
    {
        $iface = $this->config->interface->val();
        $wirelessTools = new WirelessTools($this->config);
        $wpaManager = new WpaManager($this->config);
        $wlanList = $wirelessTools->getWlanList();
        $ping = new Ping($this->config);
        $list = [
            'iwconfig (wireless-tools)' => $wirelessTools->testIwConfig(),
            'iwlist (wireless-tools)' => $wirelessTools->testIwlist(),
            'WPA Supplicant (wpasupplicant)' => $wpaManager->testBin(),
            'DHCP Client (isc-dhcp-common)' => $wpaManager->testDhcp(),
            'IP (iproute2)' => $wpaManager->testIp(),
            'KillAll (psmisc)' => $wpaManager->testKill(),
            'Ping (iputils-ping)' => $ping->testBin(),
            "Wlan interface ($iface)" => $iface && in_array($iface, $wlanList),
            'Ignore routes with linkdown' => $wpaManager->testLinkDown(),
        ];
        foreach ($list as $key => $result) {
            echo ($result ? Message::success('[+] ' . $key) : Message::error('[-] ' . $key)) . PHP_EOL;
        }

        return $this;
    }
}
