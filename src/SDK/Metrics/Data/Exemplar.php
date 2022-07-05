<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Attributes;

final class Exemplar
{
    public function __construct(
        public readonly float|int $value,
        public readonly int $timestamp,
        public readonly Attributes $attributes,
        public readonly ?string $traceId,
        public readonly ?string $spanId,
    ) {
    }
}
