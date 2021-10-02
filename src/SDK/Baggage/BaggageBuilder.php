<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Baggage;

use OpenTelemetry\API\Baggage as API;

final class BaggageBuilder implements API\BaggageBuilderInterface
{
    /** @var array<string, API\Entry> */
    private $entries;

    /** @param array<string, API\Entry> $entries */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /** @inheritDoc */
    public function remove(string $key): API\BaggageBuilderInterface
    {
        unset($this->entries[$key]);

        return $this;
    }

    /** @inheritDoc */
    public function set(string $key, $value, API\MetadataInterface $metadata = null): API\BaggageBuilderInterface
    {
        $metadata = $metadata ?? Metadata::getEmpty();

        $this->entries[$key] = new API\Entry($value, $metadata);

        return $this;
    }

    public function build(): API\BaggageInterface
    {
        return new Baggage($this->entries);
    }
}
