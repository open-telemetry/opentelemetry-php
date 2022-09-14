<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use function class_exists;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class WithSampledTraceExemplarFilter implements ExemplarFilterInterface
{
    public function accepts($value, AttributesInterface $attributes, Context $context, int $timestamp): bool
    {
        return class_exists(Span::class)
            && Span::fromContext($context)->getContext()->isSampled();
    }
}
