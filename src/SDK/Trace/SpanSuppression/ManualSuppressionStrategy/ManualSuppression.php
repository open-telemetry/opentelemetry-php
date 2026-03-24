<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\ManualSuppressionStrategy;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;

/**
 * @experimental
 */
final class ManualSuppression implements SpanSuppression
{
    public function __construct(
        private readonly ContextKeyInterface $contextKey,
    ) {
    }

    #[\Override]
    public function isSuppressed(ContextInterface $context): bool
    {
        return $context->get($this->contextKey) === true;
    }

    #[\Override]
    public function suppress(ContextInterface $context): ContextInterface
    {
        return $context;
    }
}
