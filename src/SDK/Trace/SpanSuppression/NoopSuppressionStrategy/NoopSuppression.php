<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;

/**
 * @internal
 */
final class NoopSuppression implements SpanSuppression
{
    public function isSuppressed(ContextInterface $context): bool
    {
        return false;
    }

    public function suppress(ContextInterface $context): ContextInterface
    {
        return $context;
    }
}
