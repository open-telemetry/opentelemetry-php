<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use ArrayAccess;
use function assert;
use function class_exists;
use function count;
use Countable;
use Error;
use function get_class;
use function is_object;
use IteratorAggregate;
use const PHP_VERSION_ID;
use function spl_object_id;
use function sprintf;
use Traversable;
use TypeError;
use WeakReference;

/**
 * @internal
 */
final class WeakMap implements ArrayAccess, Countable, IteratorAggregate
{
    private const KEY = '__otel_weak_map';

    /**
     * @var array<int, WeakReference>
     */
    private array $objects = [];

    private function __construct()
    {
    }

    /**
     * @return ArrayAccess&Countable&IteratorAggregate
     */
    public static function create(): ArrayAccess
    {
        if (PHP_VERSION_ID >= 80000) {
            /** @phan-suppress-next-line PhanUndeclaredClassReference */
            assert(class_exists(\WeakMap::class, false));
            /** @phan-suppress-next-line PhanUndeclaredClassMethod */
            $map = new \WeakMap();
            assert($map instanceof ArrayAccess);
            assert($map instanceof Countable);
            assert($map instanceof IteratorAggregate);

            return $map;
        }

        return new self();
    }

    public function offsetExists($offset): bool
    {
        if (!is_object($offset)) {
            throw new TypeError('WeakMap key must be an object');
        }

        return isset($offset->{self::KEY}[spl_object_id($this)]);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!is_object($offset)) {
            throw new TypeError('WeakMap key must be an object');
        }
        if (!$this->contains($offset)) {
            throw new Error(sprintf('Object %s#%d not contained in WeakMap', get_class($offset), spl_object_id($offset)));
        }

        return $offset->{self::KEY}[spl_object_id($this)];
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            throw new Error('Cannot append to WeakMap');
        }
        if (!is_object($offset)) {
            throw new TypeError('WeakMap key must be an object');
        }
        if (!$this->contains($offset)) {
            $this->expunge();
        }

        $offset->{self::KEY}[spl_object_id($this)] = $value;
        $this->objects[spl_object_id($offset)] = WeakReference::create($offset);
    }

    public function offsetUnset($offset): void
    {
        if (!is_object($offset)) {
            throw new TypeError('WeakMap key must be an object');
        }
        if (!$this->contains($offset)) {
            return;
        }

        unset(
            $offset->{self::KEY}[spl_object_id($this)],
            $this->objects[spl_object_id($offset)],
        );
        if (!$offset->{self::KEY}) {
            unset($offset->{self::KEY});
        }
    }

    public function count(): int
    {
        $this->expunge();

        return count($this->objects);
    }

    public function getIterator(): Traversable
    {
        $this->expunge();

        foreach ($this->objects as $reference) {
            if (($object = $reference->get()) && $this->contains($object)) {
                yield $object => $this[$object];
            }
        }
    }

    public function __debugInfo(): array
    {
        $debugInfo = [];
        foreach ($this as $key => $value) {
            $debugInfo[] = ['key' => $key, 'value' => $value];
        }

        return $debugInfo;
    }

    public function __destruct()
    {
        foreach ($this->objects as $reference) {
            if ($object = $reference->get()) {
                unset($this[$object]);
            }
        }
    }

    private function contains(object $offset): bool
    {
        $reference = $this->objects[spl_object_id($offset)] ?? null;
        if ($reference && $reference->get() === $offset) {
            return true;
        }

        unset($this->objects[spl_object_id($offset)]);

        return false;
    }

    private function expunge(): void
    {
        foreach ($this->objects as $id => $reference) {
            if (!$reference->get()) {
                unset($this->objects[$id]);
            }
        }
    }
}
