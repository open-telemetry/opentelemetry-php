<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use function array_key_last;
use ArrayAccess;
use Closure;
use function register_shutdown_function;

final class ShutdownHandler
{
    /** @var array<int, Closure>|null */
    private static ?array $handlers = null;
    /** @var ArrayAccess<object, self>|null  */
    private static ?ArrayAccess $weakMap = null;

    private array $ids = [];

    private function __construct()
    {
    }

    public function __destruct()
    {
        if (!self::$handlers) {
            return;
        }
        foreach ($this->ids as $id) {
            unset(self::$handlers[$id]);
        }
    }

    /**
     * Registers a function that will be executed on shutdown.
     *
     * If the given function is bound to an object, then the function will only
     * be executed if the bound object is still referenced on shutdown handler
     * invocation.
     *
     * ```php
     * ShutdownHandler::register([$tracerProvider, 'shutdown']);
     * ```
     *
     * @param callable $shutdownFunction function to register
     *
     * @see register_shutdown_function
     */
    public static function register(callable $shutdownFunction): void
    {
        self::registerShutdownFunction();
        self::$handlers[] = weaken(closure($shutdownFunction), $target);

        if (!$object = $target) {
            return;
        }

        self::$weakMap ??= WeakMap::create();
        $handler = self::$weakMap[$object] ??= new self();
        $handler->ids[] = array_key_last(self::$handlers);
    }

    private static function registerShutdownFunction(): void
    {
        if (self::$handlers === null) {
            register_shutdown_function(static function (): void {
                $handlers = self::$handlers;
                self::$handlers = null;
                self::$weakMap = null;

                // Push shutdown to end of queue
                // @phan-suppress-next-line PhanTypeMismatchArgumentInternal
                register_shutdown_function(static function (array $handlers): void {
                    foreach ($handlers as $handler) {
                        $handler();
                    }
                }, $handlers);
            });
        }
    }
}
