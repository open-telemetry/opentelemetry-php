<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeys;
use OpenTelemetry\Context\ScopeInterface;

final class Baggage implements BaggageInterface
{
    private static ?self $emptyBaggage = null;

    /** @inheritDoc */
    #[\Override]
    public static function fromContext(ContextInterface $context): BaggageInterface
    {
        return $context->get(ContextKeys::baggage()) ?? self::getEmpty();
    }

    /** @inheritDoc */
    #[\Override]
    public static function getBuilder(): BaggageBuilderInterface
    {
        return new BaggageBuilder();
    }

    /** @inheritDoc */
    #[\Override]
    public static function getCurrent(): BaggageInterface
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    #[\Override]
    public static function getEmpty(): BaggageInterface
    {
        if (null === self::$emptyBaggage) {
            self::$emptyBaggage = new self();
        }

        return self::$emptyBaggage;
    }

    /** @param array<string, Entry> $entries */
    public function __construct(private readonly array $entries = [])
    {
    }

    /** @inheritDoc */
    #[\Override]
    public function activate(): ScopeInterface
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    #[\Override]
    public function getEntry(string $key): ?Entry
    {
        return $this->entries[$key] ?? null;
    }

    /** @inheritDoc */
    #[\Override]
    public function getValue(string $key)
    {
        if (($entry = $this->getEntry($key)) !== null) {
            return $entry->getValue();
        }

        return null;
    }

    /** @inheritDoc */
    #[\Override]
    public function getAll(): iterable
    {
        foreach ($this->entries as $key => $entry) {
            yield $key => $entry;
        }
    }

    /** @inheritDoc */
    #[\Override]
    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    /** @inheritDoc */
    #[\Override]
    public function toBuilder(): BaggageBuilderInterface
    {
        return new BaggageBuilder($this->entries);
    }

    /** @inheritDoc */
    #[\Override]
    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $context->with(ContextKeys::baggage(), $this);
    }
}
