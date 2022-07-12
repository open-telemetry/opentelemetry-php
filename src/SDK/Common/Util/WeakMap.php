<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use ArrayAccess;
use function assert;
use function class_exists;
use const PHP_VERSION_ID;
use function spl_object_id;

/**
 * @internal
 */
final class WeakMap implements ArrayAccess
{
    private const KEY = '__otel_weak_map';

    private function __construct()
    {
    }

    public static function create(): ArrayAccess
    {
        if (PHP_VERSION_ID >= 80000) {
            /** @phan-suppress-next-line PhanUndeclaredClassReference */
            assert(class_exists(\WeakMap::class, false));
            /** @phan-suppress-next-line PhanUndeclaredClassMethod */
            $map = new \WeakMap();
            assert($map instanceof ArrayAccess);

            return $map;
        }

        return new self();
    }

    public function offsetExists($offset): bool
    {
        return isset($offset->{self::KEY}[spl_object_id($this)]);
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $offset->{self::KEY}[spl_object_id($this)] ?? null;
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $offset->{self::KEY}[spl_object_id($this)] = $value;
    }

    /**
     * @phan-suppress PhanUndeclaredClassAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($offset->{self::KEY}[spl_object_id($this)]);
    }
}
