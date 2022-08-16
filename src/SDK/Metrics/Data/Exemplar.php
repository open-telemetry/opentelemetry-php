<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Exemplar
{
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
     * @param float|int $value
     */
    public function __construct($value, int $timestamp, AttributesInterface $attributes, ?string $traceId, ?string $spanId)
    {
        $this->value = $value;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes;
        $this->traceId = $traceId;
        $this->spanId = $spanId;
    }
}
