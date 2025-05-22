<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\SpanSuppression;

interface SpanSuppressor {

    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression;
}
