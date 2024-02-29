<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Exemplar
{

    /**
     * @param int|string $index
     * @param float|int $value
     */
    public function __construct(
        private $index,
        /**
         * @readonly
         */
        public $value,
        /**
         * @readonly
         */
        public int $timestamp,
        /**
         * @readonly
         */
        public AttributesInterface $attributes,
        /**
         * @readonly
         */
        public ?string $traceId,
        /**
         * @readonly
         */
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
