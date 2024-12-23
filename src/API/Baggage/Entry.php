<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final readonly class Entry
{
    public function __construct(
        private mixed $value,
        private MetadataInterface $metadata,
    ) {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
