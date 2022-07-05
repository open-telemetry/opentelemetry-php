<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final class BaggageBuilder implements BaggageBuilderInterface
{
    /** @var array<string, Entry> */
    private array $entries;

    /** @param array<string, Entry> $entries */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /** @inheritDoc */
    public function remove(string $key): BaggageBuilderInterface
    {
        unset($this->entries[$key]);

        return $this;
    }

    /** @inheritDoc */
    public function set(string $key, $value, MetadataInterface $metadata = null): BaggageBuilderInterface
    {
        $metadata ??= Metadata::getEmpty();

        $this->entries[$key] = new Entry($value, $metadata);

        return $this;
    }

    public function build(): BaggageInterface
    {
        return new Baggage($this->entries);
    }
}
