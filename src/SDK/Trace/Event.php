<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function count;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class Event implements EventInterface
{
    public function __construct(
        private readonly string $name,
        private readonly int $timestamp,
        private readonly AttributesInterface $attributes,
    ) {
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
