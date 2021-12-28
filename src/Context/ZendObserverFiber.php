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

class ZendObserverFiber
{
    protected static $fibers = null;

    public function isEnabled(): bool
    {
        return (
            PHP_VERSION_ID >= 80100 &&
            (in_array(getenv('OTEL_PHP_FIBERS_ENABLED'), ['true', 'on', '1'])) &&
            class_exists(FFI::class)
        );
    }

    /**
     * @psalm-suppress UndefinedClass
     */
    public function init(): bool
    {
        if (null === self::$fibers) {
            try {
                $fibers = FFI::scope('OTEL_ZEND_OBSERVER_FIBER');
            } catch (Exception $e) {
                try {
                    $fibers = FFI::load(__DIR__ . '/fiber/zend_observer_fiber.h');
                } catch (Exception $e) {
                    return false;
                }
            }
            $fibers->zend_observer_fiber_init_register(fn (int $initializing) => Context::storage()->fork($initializing)); //@phpstan-ignore-line
            $fibers->zend_observer_fiber_switch_register(fn (int $from, int $to) => Context::storage()->switch($to)); //@phpstan-ignore-line
            $fibers->zend_observer_fiber_destroy_register(fn (int $destroying) => Context::storage()->destroy($destroying)); //@phpstan-ignore-line
            self::$fibers = $fibers;
        }

        return true;
    }
}
