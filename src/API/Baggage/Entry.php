<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final class Entry
{
    public function __construct(private mixed $value, private MetadataInterface $metadata)
    {
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
