<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;

interface LogRecordProcessorInterface
{
    public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void;
    public function shutdown(?CancellationInterface $cancellation = null): bool;
    public function forceFlush(?CancellationInterface $cancellation = null): bool;
}
