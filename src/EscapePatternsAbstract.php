<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;


abstract class EscapePatternsAbstract implements \Iterator, \Countable, \ArrayAccess
{

    protected array $collectedItems = [];

    /**
     * Handles the offsetSet method.
     *
     * @param string $string
     * @param null $offset
     * @return EscapePatternsAbstract
     */
    protected function addString(string $string, $offset = null): EscapePatternsAbstract
    {
        if (is_null($offset)) {
            $this->collectedItems[] = $string;
        } else {
            $this->collectedItems[$offset] = $string;
        }
        return $this;
    }

    public function count(): int
    {
        return count($this->collectedItems);
    }

    public function current(): ?string
    {
        $current = current($this->collectedItems);
        if ($current === false) {
            return null;
        }
        return $current;
    }

    public function key(): mixed
    {
        return key($this->collectedItems);
    }

    public function next(): void
    {
        next($this->collectedItems);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->collectedItems[$offset]);
    }

    /**
     * Ok, I'm implementing return by reference here because maybe we might want to be able to do this:
     * $obj[3][4] = 5;
     * Which requires a reference offsetGet for the [3] call.
     * See: http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * Returned object would need to also implement array access
     *
     * @param mixed $offset
     * @return string
     * @throws \Exception
     */
    public function &offsetGet(mixed $offset): string
    {
        if (!$this->offsetExists($offset)) {
            throw new \Exception('Undefined offset in EscapePatternsAbstract: ' . $offset);
        }
        return $this->collectedItems[$offset];
    }

    public function offsetSet(mixed $offset, $value): void
    {
        $this->addString($value, $offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->collectedItems[$offset]);
    }

    public function rewind(): void
    {
        reset($this->collectedItems);
    }

    public function valid(): bool
    {
        return key($this->collectedItems) !== null;
    }

    /**
     * Allows adding one array collection into another.
     * This exists because array_merge will NOT work on this, despite array_access.
     *
     * @param EscapePatternsAbstract $collection
     * @return EscapePatternsAbstract
     */
    final public function merge(EscapePatternsAbstract $collection): EscapePatternsAbstract
    {
        foreach ($collection as $item) {
            $this[] = $item;
        }
        return $this;
    }

    /**
     * Casts the collection as an array of arrays
     *
     * @return array
     */
    final public function toArray(): array
    {
        $return = [];
        foreach ($this->collectedItems as $i => $item) {
            $return[$i] = (array)$item;
        }
        return $return;
    }
}