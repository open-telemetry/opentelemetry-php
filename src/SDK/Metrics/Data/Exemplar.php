<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Exemplar
{

    /**
     * @var int|string
     */
    private $index;
    /**
     * @var float|int
     * @readonly
     */
    public $value;
    /**
     * @readonly
     */
    public int $timestamp;
    /**
     * @readonly
     */
    public AttributesInterface $attributes;
    /**
     * @readonly
     */
    public ?string $traceId;
    /**
     * @readonly
     */
    public ?string $spanId;

    /**
     * @param int|string $index
     * @param float|int $value
     */
    public function __construct($index, $value, int $timestamp, AttributesInterface $attributes, ?string $traceId, ?string $spanId)
    {
        $this->index = $index;
        $this->value = $value;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes;
        $this->traceId = $traceId;
        $this->spanId = $spanId;
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
