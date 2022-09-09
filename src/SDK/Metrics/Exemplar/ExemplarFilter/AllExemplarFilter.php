<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;

final class AllExemplarFilter implements ExemplarFilterInterface
{
    public function accepts($value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): bool
    {
        return true;
    }
}
