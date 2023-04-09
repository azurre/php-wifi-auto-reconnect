<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App\Component;

trait ArrayConverterTrait
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            $instance->{(string)$key} = $value;
        }
        return $instance;
    }
}
