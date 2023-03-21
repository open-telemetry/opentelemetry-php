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
    public static function fromContext(ContextInterface $context): BaggageInterface
    {
        return $context->get(ContextKeys::baggage()) ?? self::getEmpty();
    }

    /** @inheritDoc */
    public static function getBuilder(): BaggageBuilderInterface
    {
        return new BaggageBuilder();
    }

    /** @inheritDoc */
    public static function getCurrent(): BaggageInterface
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    public static function getEmpty(): BaggageInterface
    {
        if (null === self::$emptyBaggage) {
            self::$emptyBaggage = new self();
        }

        return self::$emptyBaggage;
    }

    /** @var array<string, Entry> */
    private array $entries;

    /** @param array<string, Entry> $entries */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /** @inheritDoc */
    public function activate(): ScopeInterface
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    public function getEntry(string $key): ?Entry
    {
        return $this->entries[$key] ?? null;
    }

    /** @inheritDoc */
    public function getValue(string $key)
    {
        if (($entry = $this->getEntry($key)) !== null) {
            return $entry->getValue();
        }

        return null;
    }

    /** @inheritDoc */
    public function getAll(): iterable
    {
        foreach ($this->entries as $key => $entry) {
            yield $key => $entry;
        }
    }

    /** @inheritDoc */
    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    /** @inheritDoc */
    public function toBuilder(): BaggageBuilderInterface
    {
        return new BaggageBuilder($this->entries);
    }

    /** @inheritDoc */
    public function storeInContext(ContextInterface $context): ContextInterface
    {
        return $context->with(ContextKeys::baggage(), $this);
    }
}
