<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionSuppressionStrategy;

use OpenTelemetry\Context\ContextKeyInterface;

/**
 * @implements ContextKeyInterface<array<string, true>>
 *
 * @internal
 */
enum SemanticConventionSuppressionContextKey implements ContextKeyInterface
{
    case Internal;
    case Client;
    case Server;
    case Producer;
    case Consumer;
}
