<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Baggage;

use OpenTelemetry\Baggage as API;

final class BaggageBuilder implements API\BaggageBuilder
{
    /** @var array<string, API\Entry> */
    private $entries;

    /** @param array<string, API\Entry> $entries */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /** @inheritDoc */
    public function remove(string $key): API\BaggageBuilder
    {
        unset($this->entries[$key]);

        return $this;
    }

    /** @inheritDoc */
    public function set(string $key, $value, API\Metadata $metadata = null): API\BaggageBuilder
    {
        $metadata = $metadata ?? Metadata::getEmpty();

        $this->entries[$key] = new API\Entry($value, $metadata);

        return $this;
    }

    public function build(): API\Baggage
    {
        return new Baggage($this->entries);
    }
}
