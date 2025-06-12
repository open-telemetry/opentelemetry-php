<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

interface LoggerInterface
{
    public function emit(LogRecord $logRecord): void;

    /**
     * Determine if the logger is enabled. Instrumentation authors SHOULD call this method each time they
     * emit a LogRecord, to ensure they have the most up-to-date response.
     */
    public function isEnabled(?ContextInterface $context = null, ?int $severityNumber = null): bool;
}
