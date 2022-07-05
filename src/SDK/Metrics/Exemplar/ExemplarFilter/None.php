<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

final class None implements ExemplarFilter
{
    public function accepts($value, Attributes $attributes, Context $context, int $timestamp): bool
    {
        return false;
    }
}
