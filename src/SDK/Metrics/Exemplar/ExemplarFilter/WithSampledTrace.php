<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use function class_exists;
use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

final class WithSampledTrace implements ExemplarFilter
{
    public function accepts(float|int $value, Attributes $attributes, Context $context, int $timestamp): bool
    {
        return class_exists(AbstractSpan::class)
            && AbstractSpan::fromContext($context)->getContext()->isSampled();
    }
}
