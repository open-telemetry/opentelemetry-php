<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy;

use OpenTelemetry\Context\ContextKeyInterface;

/**
 * @implements ContextKeyInterface<true>
 *
 * @internal
 */
enum SpanKindSuppressionContextKey implements ContextKeyInterface
{
    case Client;
    case Server;
    case Producer;
    case Consumer;
}
