<?php

declare (strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;

/**
 * @experimental
 */
interface SpanSuppressionStrategyInterface extends ImplicitContextKeyedInterface
{
    public function shouldSuppress(
        int $spanKind,
        array $attributes = [],
        ?ContextInterface $context = null,
    ): bool;

    public static function current(?ContextInterface $context = null): self;
    public static function default(): self;
}
