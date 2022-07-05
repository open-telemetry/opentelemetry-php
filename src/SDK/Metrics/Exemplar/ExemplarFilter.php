<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;

interface ExemplarFilter
{
    public function accepts(float|int $value, Attributes $attributes, Context $context, int $timestamp): bool;
}
