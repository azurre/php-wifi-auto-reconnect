<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App\Model\Data;

/**
 * @property-read string $essid
 * @property-read int|null $channel
 * @property-read float|null $frequency
 * @property-read float|int|null $signal
 */
interface AccessPointInterface
{
    /** @return array{essid:string, channel:int|null, frequency:float|null, signal:int|float|null} */
    public function toArray(): array;

    /**
     * @param array{essid:string, channel:int|null, frequency:float|null, signal:int|float|null} $data
     * @return static
     */
    public static function fromArray(array $data): static;
}
