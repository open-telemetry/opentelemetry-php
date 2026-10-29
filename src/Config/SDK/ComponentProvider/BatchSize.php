<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Context;
use Psr\Log\LogLevel;
use function sprintf;

/**
 * Lowers the batch size to the queue size before the batch processors do, so that the warning
 * goes through `$context->logger` and honours `log_level`.
 *
 * @internal
 */
final class BatchSize
{
    public static function clamp(int $maxExportBatchSize, int $maxQueueSize, Context $context): int
    {
        if ($maxExportBatchSize <= $maxQueueSize) {
            return $maxExportBatchSize;
        }

        $context->logger->log(LogLevel::WARNING, sprintf('Maximum export batch size (%d) exceeds maximum queue size (%d), using %2$d', $maxExportBatchSize, $maxQueueSize));

        return $maxQueueSize;
    }
}
