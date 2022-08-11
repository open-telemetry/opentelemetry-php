<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use function class_exists;
use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class WithSampledTraceExemplarFilter implements ExemplarFilterInterface
{
    public function accepts($value, AttributesInterface $attributes, Context $context, int $timestamp): bool
    {
        return class_exists(AbstractSpan::class)
            && AbstractSpan::fromContext($context)->getContext()->isSampled();
    }
}
