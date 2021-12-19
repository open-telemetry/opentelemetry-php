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
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;

class ZendObserverFiber
{
    use LogsMessagesTrait;
    use EnvironmentVariablesTrait;

    protected static $fibers = null;

    public function isEnabled(): bool
    {
        return (
            PHP_VERSION_ID >= 80100 &&
            $this->getBooleanFromEnvironment('OTEL_PHP_FIBERS_ENABLED', false) &&
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
                $fibers = FFI::load(__DIR__ . '/fiber/zend_observer_fiber.h');
                $fibers->zend_observer_fiber_init_register(fn (int $initializing) => Context::storage()->fork($initializing)); //@phpstan-ignore-line
                $fibers->zend_observer_fiber_switch_register(fn (int $from, int $to) => Context::storage()->switch($to)); //@phpstan-ignore-line
                $fibers->zend_observer_fiber_destroy_register(fn (int $destroying) => Context::storage()->destroy($destroying)); //@phpstan-ignore-line
                self::$fibers = $fibers;
            } catch (Exception $e) {
                $this->logWarning('Error setting up FFI fiber observer', ['error' => $e]);

                return false;
            }
        }

        return true;
    }
}
