<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression\Strategy;

use OpenTelemetry\API\Trace\SpanSuppression\SpanSuppressionStrategyInterface;
use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
class SpanKindSuppressionStrategy implements SpanSuppressionStrategyInterface
{
    use StrategyTrait;

    private function __construct(
        private readonly array $suppressedSpanKinds = [],
    ) {
    }

    public static function suppressSpanKind(int $spanKind, ?ContextInterface $context = null): self
    {
        $new = new self([$spanKind]);

        return self::current($context)->mergeWith($new);
    }

    public function shouldSuppress(int $spanKind, array $attributes = [], ?ContextInterface $context = null): bool
    {
        return in_array($spanKind, $this->suppressedSpanKinds, true);
    }

    private function mergeWith(self $other): self
    {
        return new self(
            array_unique(array_merge($this->suppressedSpanKinds, $other->suppressedSpanKinds))
        );
    }
}
