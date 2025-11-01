<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

/**
 * @internal
 */
final class SpanKindSuppressor implements SpanSuppressor
{
    #[\Override]
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        static $suppressions = [];

        return $suppressions[$spanKind] ??= match ($spanKind) {
            SpanKind::KIND_INTERNAL => new NoopSuppression(),
            SpanKind::KIND_CLIENT => new SpanKindSuppression(SpanKindSuppressionContextKey::Client),
            SpanKind::KIND_SERVER => new SpanKindSuppression(SpanKindSuppressionContextKey::Server),
            SpanKind::KIND_PRODUCER => new SpanKindSuppression(SpanKindSuppressionContextKey::Producer),
            SpanKind::KIND_CONSUMER => new SpanKindSuppression(SpanKindSuppressionContextKey::Consumer),
        };
    }
}
