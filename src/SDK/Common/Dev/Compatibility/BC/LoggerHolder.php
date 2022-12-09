<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\API\Common\Log\LoggerHolder as Moved;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnoreStart
 */
const OpenTelemetry_SDK_Common_Log_LoggerHolder = 'OpenTelemetry\SDK\Common\Log\LoggerHolder';

final class LoggerHolder
{
    public static function set(LoggerInterface $logger): void
    {
        Moved::set($logger);
        Util::triggerClassDeprecationNotice(
            OpenTelemetry_SDK_Common_Log_LoggerHolder,
            Moved::class
        );
    }
}

class_alias(LoggerHolder::class, OpenTelemetry_SDK_Common_Log_LoggerHolder);
/**
 * @codeCoverageIgnoreEnd
 */
