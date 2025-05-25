<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppression;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;

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

    public function suppress(ContextInterface $context): ContextInterface
    {
        $suppressedConventions = $context->get($this->contextKey) ?? [];
        foreach ($this->semanticConventions as $semanticConvention) {
            $suppressedConventions[$semanticConvention] ??= true;
        }

        return $context->with($this->contextKey, $suppressedConventions);
    }
}
