<?php

declare(strict_types=1);

namespace OpenTelemetry\Baggage;

final class Entry
{
    /** @var mixed */
    private $value;

    /** @var Metadata|null */
    private $metadata;

    /**
     * @param mixed $value
     * @param Metadata|null $metadata
     */
    public function __construct(
        $value,
        ?Metadata $metadata
    ) {
        $this->value = $value;
        $this->metadata = $metadata;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }
}
