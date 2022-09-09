<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class WithSampledTraceExemplarFilter implements ExemplarFilterInterface
{
    public function accepts($value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): bool
    {
        return Span::fromContext($context)->getContext()->isSampled();
    }
}
