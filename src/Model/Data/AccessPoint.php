<?php
/**
 * @author Alex Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Alex Milenin (https://azrr.info/)
 */


namespace App\Model\Data;

use App\Component\ArrayConverterTrait;

class AccessPoint implements AccessPointInterface
{
    use ArrayConverterTrait;

    public string $essid;
    public int $channel;
    public float $frequency;
    public float|int $signal;

    public function __construct(string $essid = null, int|float $signal = null, float $frequency = null, int $channel = null)
    {
        if ($essid) {
            $this->essid = $essid;
        }
        if ($signal !== null) {
            $this->signal = $signal;
        }
        if ($frequency) {
            $this->frequency = $frequency;
        }
        if ($channel) {
            $this->channel = $channel;
        }
    }
}
