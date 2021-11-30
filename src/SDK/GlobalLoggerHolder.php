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
     * Whenever the library suppresses an error that would otherwise have been exposed
     * to the user, the library SHOULD log the error using language-specific conventions.
     */
    public static function get(): LoggerInterface
    {
        if (null === self::$logger) {
            //TODO build a stdout logger from env vars?
            $level = getenv('OTEL_LOG_LEVEL') ?: 'info';

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
