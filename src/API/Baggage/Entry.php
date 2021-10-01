<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final class Entry
{
    /** @var mixed */
    private $value;

    /** @var Metadata */
    private $metadata;

    /**
     * @param mixed $value
     * @param Metadata $metadata
     */
    public function __construct(
        $value,
        Metadata $metadata
    ) {
        $this->value = $value;
        $this->metadata = $metadata;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
}
