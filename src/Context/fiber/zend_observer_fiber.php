<?php

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

declare(strict_types=1);

use OpenTelemetry\Context\Context;

if (PHP_VERSION_ID < 80100 || !class_exists(FFI::class)) {
    return false;
}

return (function (): bool {
    try {
        $fibers = FFI::scope('OTEL_ZEND_OBSERVER_FIBER');
    } catch (FFI\Exception $e) {
        try {
            $fibers = FFI::load(__DIR__ . '/zend_observer_fiber.h');
        } catch (FFI\Exception $e) {
            return false;
        }

        // Has to keep reference alive
        define(__NAMESPACE__ . '\\_ffi_fibers', $fibers);
    }

    $fibers->zend_observer_fiber_init_register(fn (int $initializing) => Context::storage()->fork($initializing));
    $fibers->zend_observer_fiber_switch_register(fn (int $from, int $to) => Context::storage()->switch($to));
    $fibers->zend_observer_fiber_destroy_register(fn (int $destroying) => Context::storage()->destroy($destroying));

    return true;
})();
