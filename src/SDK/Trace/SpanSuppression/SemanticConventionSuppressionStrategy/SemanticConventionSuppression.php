<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;

/**
 * @internal
 */
final class SemanticConventionSuppression implements SpanSuppression
{
    public function __construct(
        private readonly ContextKeyInterface $contextKey,
        private readonly array $semanticConventions,
    ) {
    }

    #[\Override]
    public function isSuppressed(ContextInterface $context): bool
    {
        $suppressedConventions = $context->get($this->contextKey);
        if ($suppressedConventions === null) {
            return false;
        }

        foreach ($this->semanticConventions as $semanticConvention) {
            if (!isset($suppressedConventions[$semanticConvention])) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function suppress(ContextInterface $context): ContextInterface
    {
        $suppressedConventions = $context->get($this->contextKey) ?? [];
        foreach ($this->semanticConventions as $semanticConvention) {
            $suppressedConventions[$semanticConvention] ??= true;
        }

        return $context->with($this->contextKey, $suppressedConventions);
    }
}
