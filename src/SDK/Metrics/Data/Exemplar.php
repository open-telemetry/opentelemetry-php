<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Exemplar
{
    public function __construct(
        private int|string $index,
        /** @readonly */
        public float|int $value,
        /** @readonly */
        public int $timestamp,
        /** @readonly */
        public AttributesInterface $attributes,
        /** @readonly */
        public ?string $traceId,
        /** @readonly */
        public ?string $spanId
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
