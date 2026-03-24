<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\ManualSuppressionStrategy;

use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

/**
 * @experimental
 */
final class ManualSuppressor implements SpanSuppressor
{
    #[\Override]
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        static $suppression = new ManualSuppression(ManualSuppressionContextKey::Suppress);

        return $suppression;
    }
}
