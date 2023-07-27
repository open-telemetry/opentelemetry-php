<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

/**
 * The exemplar spec is not yet stable, and can change at any time.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk.md#exemplar
 */
final class WithSampledTraceExemplarFilter implements ExemplarFilterInterface
{
    public function accepts($value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): bool
    {
        return Span::fromContext($context)->getContext()->isSampled();
    }
}
