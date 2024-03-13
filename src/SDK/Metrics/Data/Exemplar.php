<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Exemplar
{
    public function __construct(
        private readonly int|string $index,
        public readonly float|int $value,
        public readonly int $timestamp,
        public readonly AttributesInterface $attributes,
        public readonly ?string $traceId,
        public readonly ?string $spanId,
    ) {
    }

    /**
     * @param iterable<Exemplar> $exemplars
     * @return array<list<Exemplar>>
     */
    public static function groupByIndex(iterable $exemplars): array
    {
        $grouped = [];
        foreach ($exemplars as $exemplar) {
            $grouped[$exemplar->index][] = $exemplar;
        }

        return $grouped;
    }
}
