<?php

/** @noinspection PhpUndefinedMethodInspection */
/** @phan-file-suppress PhanUndeclaredClassCatch */
/** @phan-file-suppress PhanUndeclaredClassMethod */
/** @phan-file-suppress PhanUndeclaredMethod */

declare(strict_types=1);

namespace OpenTelemetry\Context;

use function extension_loaded;
use FFI;
use const FILTER_VALIDATE_BOOLEAN;
use function filter_var;
use function is_string;
use const PHP_VERSION_ID;
use const PHP_ZTS;
use function sprintf;
use function trigger_error;

/**
 * @internal
 */
final class ZendObserverFiber
{
    public static function isEnabled(): bool
    {
        $enabled = $_SERVER['OTEL_PHP_FIBERS_ENABLED'] ?? false;

        return is_string($enabled)
            ? filter_var($enabled, FILTER_VALIDATE_BOOLEAN)
            : (bool) $enabled;
    }

    /**
     * @psalm-suppress PossiblyNullReference,UndefinedMethod
     */
    public static function init(): bool
    {
        static $fibers;
        if ($fibers) {
            return true;
        }

        if (PHP_ZTS || PHP_VERSION_ID < 80100 || !extension_loaded('ffi')) {
            trigger_error('Context: Fiber context switching not supported, requires PHP >= 8.1, an NTS build, and the FFI extension');

            return false;
        }

        try {
            $fibers = FFI::scope('OTEL_ZEND_OBSERVER_FIBER');
        } catch (FFI\Exception) {
            try {
                $fibers = FFI::load(__DIR__ . '/fiber/zend_observer_fiber.h');
            } catch (FFI\Exception $e) {
                trigger_error(sprintf('Context: Fiber context switching not supported, %s', $e->getMessage()));

                return false;
            }
        }

        $storage = new ContextStorage();
        $fibers->zend_observer_fiber_init_register(static fn (int $initializing) => $storage->fork($initializing)); //@phpstan-ignore-line
        $fibers->zend_observer_fiber_switch_register(static fn (int $from, int $to) => $storage->switch($to)); //@phpstan-ignore-line
        $fibers->zend_observer_fiber_destroy_register(static fn (int $destroying) => $storage->destroy($destroying)); //@phpstan-ignore-line

        Context::setStorage($storage);

        return true;
    }
}
