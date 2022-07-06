<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

interface ExemplarFilter
{
    /**
     * @param float|int $value
     */
    public function accepts($value, AttributesInterface $attributes, Context $context, int $timestamp): bool;
}
