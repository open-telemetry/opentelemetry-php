<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

interface ExemplarFilterInterface
{
    /**
     * @param float|int $value
     */
    public function accepts($value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): bool;
}
