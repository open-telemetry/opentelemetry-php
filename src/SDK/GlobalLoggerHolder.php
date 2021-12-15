<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class GlobalLoggerHolder
{
    private static ?LoggerInterface $logger = null;

    /**
     * @suppress PhanTypeMismatchReturnNullable
     */
    public static function get(): LoggerInterface
    {
        if (null === self::$logger) {
            //TODO a sensible default, instead of NullLogger, could be to log to stdout

            return new NullLogger();
        }

        return self::$logger;
    }

    public static function set(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function isSet(): bool
    {
        return null !== self::$logger;
    }

    public static function unset(): void
    {
        self::$logger = null;
    }

    /**
     * Disable logging
     */
    public static function disable(): void
    {
        self::$logger = new NullLogger();
    }
}
