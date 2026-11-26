<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

/**
 * @experimental
 */
interface ExtendedLogRecordProcessorInterface extends LogRecordProcessorInterface
{
    /**
     * Returns false if the processor would drop a record with these parameters. Returns true if it
     * would keep the record, or if it can't tell.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#enabled-1
     */
    public function isEnabled(
        ContextInterface $context,
        InstrumentationScopeInterface $scope,
        ?int $severityNumber,
        ?string $eventName,
    ): bool;
}
