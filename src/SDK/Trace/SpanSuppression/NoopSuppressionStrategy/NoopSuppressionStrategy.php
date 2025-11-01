<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy;

use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

/**
 * @experimental
 */
final class NoopSuppressionStrategy implements SpanSuppressionStrategy
{
    #[\Override]
    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor
    {
        static $suppressor = new NoopSuppressor();

        return $suppressor;
    }
}
