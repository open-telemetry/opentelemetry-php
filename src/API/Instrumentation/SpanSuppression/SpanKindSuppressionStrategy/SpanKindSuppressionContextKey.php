<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\SpanSuppression\SpanKindSuppressionStrategy;

use OpenTelemetry\Context\ContextKeyInterface;

/**
 * @implements ContextKeyInterface<true>
 */
enum SpanKindSuppressionContextKey implements ContextKeyInterface {

    case Client;
    case Server;
    case Producer;
    case Consumer;
}
