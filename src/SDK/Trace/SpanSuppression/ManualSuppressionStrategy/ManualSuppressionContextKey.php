<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\ManualSuppressionStrategy;

use OpenTelemetry\Context\ContextKeyInterface;

/**
 * @experimental
 *
 * @implements ContextKeyInterface<true>
 */
enum ManualSuppressionContextKey implements ContextKeyInterface
{
    case Suppress;
}
