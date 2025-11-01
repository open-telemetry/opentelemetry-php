<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy;

use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

/**
 * @internal
 */
final class NoopSuppressor implements SpanSuppressor
{
    #[\Override]
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        static $suppression = new NoopSuppression();

        return $suppression;
    }
}
