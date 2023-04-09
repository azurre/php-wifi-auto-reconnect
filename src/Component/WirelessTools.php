<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App\Component;

use App\Config;
use App\Model\Data\AccessPoint;
use App\Model\Data\AccessPointInterface;
use Azurre\Component\System\Shell;

class WirelessTools
{
    protected Config $config;
    protected string $iwconfigBin = 'iwconfig';
    protected string $iwlistBin = 'iwlist';

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected array $parseProps = [
        ['key' => 'essid'],
        ['key' => 'channel'],
        ['key' => 'frequency', 'regex' => '/Frequency:\s*([\d\.]+)/i'],
        ['key' => 'signal', 'regex' => '/Signal level=\s*([\-\d\.]+)/i']
    ];

    protected function getRegex(string $key): string
    {
        return "/$key:(.*)/i";
    }

    /**
     * @return AccessPointInterface[]
     */
    public function getApList(): array
    {
        $listArray = $this->parseScanResult($this->executeScan());
        $list = array_map(fn($res) => AccessPoint::fromArray($res), $listArray);
        usort($list, fn(AccessPointInterface $a, AccessPoint $b) => $b->signal <=> $a->signal);
        return $list;
    }

    /**
     * @param string $result
     * @return array{essid:string, channel:int|null, frequency:float|null, signal:int|float|null}
     */
    protected function parseScanResult(string $result): array
    {
        $idx = 0;
        $out = [];
        $list = explode('Cell ', $result);
        foreach ($list as $item) {
            foreach ($this->parseProps as $prop) {
                $regex = $prop['regex'] ?? $this->getRegex($prop['key']);
                preg_match($regex, $item, $matches);
                if (sizeof($matches) === 2) {
                    if (!isset($out[$idx])) {
                        $out[$idx] = [];
                    }
                    $out[$idx][$prop['key']] = trim(trim($matches[1]), "\"");
                }
            }
            if (isset($out[$idx])) {
                $idx++;
            }
        }
        return $out;
    }

    protected function executeScan(): string
    {
        $iface = $this->config->interface->val();
        $cmd = "$this->iwlistBin $iface scan";
        return Shell::create()->run($cmd)->getStdOut();
    }

    /**
     * @return string[] Wlan list
     */
    public function getWlanList(): array
    {
        $regex = '/(\w+)\s+IEEE/i';
        $shell = Shell::create()->run($this->iwconfigBin);
        if (!$shell->getExitCode()) {
            $out = $shell->getStdOut();
            if (preg_match_all($regex, $out, $matches)) {
                return (array)$matches[1] ?? [];
            }
        }

        return [];
    }

    public function testIwConfig(): bool
    {
        return !Shell::create()->run($this->iwconfigBin)->getExitCode();
    }

    public function testIwlist(): bool
    {
        return !Shell::create()->run("$this->iwlistBin --help")->getExitCode();
    }
}
