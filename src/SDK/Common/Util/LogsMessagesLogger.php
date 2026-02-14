<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class LogsMessagesLogger extends AbstractLogger implements LoggerInterface
{
    use LogsMessagesTrait;

    #[\Override]
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        self::doLog((string) $level, (string) $message, $context);
    }
}
