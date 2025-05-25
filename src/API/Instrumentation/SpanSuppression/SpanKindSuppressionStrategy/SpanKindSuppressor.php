<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression\SpanKindSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\NoopSuppressionStrategy\NoopSuppression;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppression;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressor;
use OpenTelemetry\API\Trace\SpanKind;

final class SpanKindSuppressor implements SpanSuppressor
{
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        return match ($spanKind) {
            SpanKind::KIND_INTERNAL => new NoopSuppression(),
            SpanKind::KIND_CLIENT => new SpanKindSuppression(SpanKindSuppressionContextKey::Client),
            SpanKind::KIND_SERVER => new SpanKindSuppression(SpanKindSuppressionContextKey::Server),
            SpanKind::KIND_PRODUCER => new SpanKindSuppression(SpanKindSuppressionContextKey::Producer),
            SpanKind::KIND_CONSUMER => new SpanKindSuppression(SpanKindSuppressionContextKey::Consumer),
        };
    }
}
