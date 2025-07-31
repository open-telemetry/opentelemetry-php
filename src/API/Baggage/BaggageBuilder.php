<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final class BaggageBuilder implements BaggageBuilderInterface
{
    /** @param array<string, Entry> $entries */
    public function __construct(private array $entries = [])
    {
    }

    /** @inheritDoc */
    #[\Override]
    public function remove(string $key): BaggageBuilderInterface
    {
        unset($this->entries[$key]);

        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function set(string $key, $value, ?MetadataInterface $metadata = null): BaggageBuilderInterface
    {
        if ($key === '') {
            return $this;
        }
        $metadata ??= Metadata::getEmpty();

        $this->entries[$key] = new Entry($value, $metadata);

        return $this;
    }

    #[\Override]
    public function build(): BaggageInterface
    {
        return new Baggage($this->entries);
    }
}
