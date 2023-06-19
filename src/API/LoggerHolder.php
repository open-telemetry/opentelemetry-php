<?php

declare(strict_types=1);

namespace OpenTelemetry\API;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class LoggerHolder
{
    private static ?LoggerInterface $logger = null;

    /**
     * This constructor is a temporary solution to ease the setup of the logger with DI libraries
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        self::$logger = $logger;
    }

    /**
     * @suppress PhanTypeMismatchReturnNullable
     * @internal
     */
    public static function get(): ?LoggerInterface
    {
        return self::$logger;
    }

    public static function set(?LoggerInterface $logger): void
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
     * Disable psr-3 logging
     */
    public static function disable(): void
    {
        self::$logger = new NullLogger();
    }
}
