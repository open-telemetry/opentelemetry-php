<?php

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @phan-file-suppress PhanUndeclaredClassReference */
/** @phan-file-suppress PhanUndeclaredClassCatch */
/** @phan-file-suppress PhanUndeclaredClassMethod */
/** @phan-file-suppress PhanUndeclaredMethod */

declare(strict_types=1);

namespace OpenTelemetry\Context;

use FFI;
use FFI\Exception;

if (PHP_VERSION_ID < 80100 || !class_exists(FFI::class)) {
    return false;
}

/** @psalm-suppress UndefinedClass */
return (function (): bool {
    try {
        $fibers = FFI::scope('OTEL_ZEND_OBSERVER_FIBER');
    } catch (Exception $e) {
        try {
            $fibers = FFI::load(__DIR__ . '/zend_observer_fiber.h');
        } catch (Exception $e) {
            return false;
        }

        // @internal
        class FFIFiberHolder
        {
            public static FFI $fibers;
        }
        // Has to keep reference alive so that it is not garbage collected
        FFIFiberHolder::$fibers = $fibers;
    }

    $fibers->zend_observer_fiber_init_register(fn (int $initializing) => Context::storage()->fork($initializing)); // @phpstan-ignore-line
    $fibers->zend_observer_fiber_switch_register(fn (int $from, int $to) => Context::storage()->switch($to)); // @phpstan-ignore-line
    $fibers->zend_observer_fiber_destroy_register(fn (int $destroying) => Context::storage()->destroy($destroying)); // @phpstan-ignore-line

    return true;
})();
