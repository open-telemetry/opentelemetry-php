<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression\NoopSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppression;
use OpenTelemetry\Context\ContextInterface;

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
