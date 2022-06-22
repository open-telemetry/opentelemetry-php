<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\API\Common\Log\LoggerHolder as Moved;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnoreStart
 */
const OpenTelemetry_SDK_LoggerHolder = 'OpenTelemetry\SDK\LoggerHolder';

class LoggerHolder
{
    public static function get(): LoggerInterface
    {
        self::triggerClassDeprecationNotice();

        return Moved::get();
    }

    public static function set(LoggerInterface $logger): void
    {
        self::triggerClassDeprecationNotice();

        Moved::set($logger);
    }

    public static function isSet(): bool
    {
        self::triggerClassDeprecationNotice();

        return Moved::isSet();
    }

    public static function unset(): void
    {
        self::triggerClassDeprecationNotice();

        Moved::unset();
    }

    public static function disable(): void
    {
        self::triggerClassDeprecationNotice();

        Moved::disable();
    }

    private static function triggerClassDeprecationNotice(): void
    {
        Util::triggerClassDeprecationNotice(
            OpenTelemetry_SDK_LoggerHolder,
            Moved::class
        );
    }
}

class_alias(LoggerHolder::class, OpenTelemetry_SDK_LoggerHolder);
/**
 * @codeCoverageIgnoreEnd
 */
