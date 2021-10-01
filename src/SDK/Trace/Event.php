<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use OpenTelemetry\API\Trace as API;

final class Event implements API\Event
{
    private string $name;
    private int $timestamp;
    private API\Attributes $attributes;
    private int $totalAttributeCount;

    public function __construct(string $name, int $timestamp, API\Attributes $attributes = null)
    {
        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes ?? new Attributes();
        $this->totalAttributeCount = count($this->attributes);
    }

    public function getAttributes(): API\Attributes
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
