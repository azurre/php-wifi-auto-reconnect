<?php

namespace App\Component;

/**
 * Class SmartObject
 */
class SmartObject implements \Iterator, \JsonSerializable
{
    protected array $data = [];
    protected ?string $pointer = null;

    public function __construct(array $data = null)
    {
        $this->data = $data ?? [];
    }

    public function __get(string $path): static
    {
        if (empty($this->pointer)) {
            $this->pointer = $path;
        } else {
            $this->pointer .= ".$path";
        }

        return $this;
    }

    public function __set(string $path, mixed $value): void
    {
        if (empty($this->pointer)) {
            $this->pointer = $path;
        } else {
            $this->pointer .= ".$path";
        }
        $reference = &$this->data;
        foreach (explode('.', $this->pointer) as $key) {
            if (!array_key_exists($key, $reference)) {
                $reference[$key] = [];
            }
            $reference = &$reference[$key];
        }
        $reference = $value;
        $this->reset();
    }

    public function __isset(string $path): bool
    {
        return static::getByPath($this->data, $this->pointer) !== null;
    }

    public function merge(array $data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function val(string $pointer = null)
    {
        if ($pointer) {
            $this->pointer = $pointer;
        }
        $data = static::getByPath($this->data, $this->pointer);
        $this->reset();

        return $data;
    }

    public function reset(): static
    {
        $this->pointer = null;

        return $this;
    }

    /**
     * Errorless method to get complex key from array (path.to.array.key)
     */
    public static function getByPath(array $array, string $key = null, mixed $default = null): mixed
    {
        if (empty($key) && (string)$key !== '0') {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return is_callable($default) ? $default($key) : $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    public function toArray(): array
    {
        return (array)$this->data;
    }

    public function __toString(): string
    {
        return (string)$this->val();
    }

    /**
     * @inheritDoc
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function key(): string|int|null
    {
        return key($this->data);
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return null !== key($this->data);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): string|false
    {
        return json_encode($this->data);
    }
}
