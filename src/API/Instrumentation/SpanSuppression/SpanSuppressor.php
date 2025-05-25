<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression;

interface SpanSuppressor
{
    /**
     * @param int<0, 4> $spanKind
     */
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression;
}
