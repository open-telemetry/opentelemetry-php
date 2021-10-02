<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use OpenTelemetry\API\Trace as API;

final class Event implements API\EventInterface
{
    private string $name;
    private int $timestamp;
    private API\AttributesInterface $attributes;
    private int $totalAttributeCount;

    public function __construct(string $name, int $timestamp, API\AttributesInterface $attributes = null)
    {
        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes ?? new Attributes();
        $this->totalAttributeCount = count($this->attributes);
    }

    public function getAttributes(): API\AttributesInterface
    {
        return $this->attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEpochNanos(): int
    {
        return $this->timestamp;
    }

    public function getTotalAttributeCount(): int
    {
        return $this->totalAttributeCount;
    }

    public function getDroppedAttributesCount(): int
    {
        return $this->totalAttributeCount - count($this->attributes);
    }
}
