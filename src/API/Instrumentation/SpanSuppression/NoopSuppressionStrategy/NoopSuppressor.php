<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression\NoopSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppression;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressor;

/**
 * @internal
 */
final class NoopSuppressor implements SpanSuppressor
{
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        return new NoopSuppression();
    }
}
