<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class AllExemplarFilter implements ExemplarFilterInterface
{
    public function accepts($value, AttributesInterface $attributes, Context $context, int $timestamp): bool
    {
        return true;
    }
}
