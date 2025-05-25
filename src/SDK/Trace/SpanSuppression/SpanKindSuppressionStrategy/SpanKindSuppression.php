<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;

/**
 * @internal
 */
final class SpanKindSuppression implements SpanSuppression
{
    public function __construct(
        private readonly ContextKeyInterface $contextKey,
    ) {
    }

    public function isSuppressed(ContextInterface $context): bool
    {
        return $context->get($this->contextKey) === true;
    }

    public function suppress(ContextInterface $context): ContextInterface
    {
        return $context->with($this->contextKey, true);
    }
}
