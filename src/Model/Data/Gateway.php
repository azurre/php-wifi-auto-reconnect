<?php
/**
 * @author Alex Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Alex Milenin (https://azrr.info/)
 */


namespace App\Model\Data;

/**
 * @property-read string $ip
 * @property-read string $iface
 * @property int|null $priority
 */
class Gateway
{
    const MODE_DEFAULT = 'default';
    const MODE_WLAN = 'wlan';
    const MODE_BALANCED = 'balanced';
    const MODE_ETH_PRIORITY = 'eth-priority';
    const MODE_WLAN_PRIORITY = 'wlan-priority';

    public string $ip;
    public string $iface;
    public ?int $priority;

    public function __construct(string $ip, string $iface, int $priority = null)
    {
        $this->ip = $ip;
        $this->iface = $iface;
        $this->priority = $priority;
    }
}
