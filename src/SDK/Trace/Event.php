<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use OpenTelemetry\API\AttributesInterface;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Attributes;

final class Event implements API\EventInterface
{
    private string $name;
    private int $timestamp;
    private AttributesInterface $attributes;

    public function __construct(string $name, int $timestamp, AttributesInterface $attributes = null)
    {
        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->attributes = $attributes ?? new Attributes();
    }

    public function getAttributes(): AttributesInterface
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
        return count($this->attributes);
    }

    public function getDroppedAttributesCount(): int
    {
        return $this->attributes->getDroppedAttributesCount();
    }
}
