<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final class Entry
{
    /** @var mixed */
    private $value;

    private MetadataInterface $metadata;

    /**
     * @param mixed $value
     * @param MetadataInterface $metadata
     */
    public function __construct(
        $value,
        MetadataInterface $metadata
    ) {
        $this->value = $value;
        $this->metadata = $metadata;
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
