<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Baggage;

use OpenTelemetry\Baggage as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Scope;
use OpenTelemetry\Sdk\Trace\BaggageContextKey;

final class Baggage implements API\Baggage
{
    /** @var self|null */
    private static $emptyBaggage;

    /**
     * @todo Implement this in the API layer
     */
    public static function fromContext(Context $context): API\Baggage
    {
        if ($baggage = $context->get(BaggageContextKey::instance())) {
            return $baggage;
        }

        return self::getEmpty();
    }

    /**
     * @todo Implement this in the API layer
     */
    public static function getCurrent(): API\Baggage
    {
        return self::fromContext(Context::getCurrent());
    }

    public static function getEmpty(): API\Baggage
    {
        if (null === self::$emptyBaggage) {
            self::$emptyBaggage = new self();
        }

        return self::$emptyBaggage;
    }

    /** @var array<string, API\Entry> */
    private $entries;

    /** @param array<string, API\Entry> $entries */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /** @inheritDoc */
    public function activate(): Scope
    {
        return Context::getCurrent()->withContextValue($this)->activate();
    }

    /** @inheritDoc */
    public function storeInContext(Context $context): Context
    {
        return $context->with(BaggageContextKey::instance(), $this);
    }

    /** @inheritDoc */
    public function getEntry(string $key): ?API\Entry
    {
        return $this->entries[$key] ?? null;
    }

    public function getValue(string $key)
    {
        if ($entry = $this->getEntry($key)) {
            return $entry->getValue();
        }

        return null;
    }

    /** @inheritDoc */
    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /** @inheritDoc */
    public function set(string $key, $value, ?API\Metadata $metadata = null): API\Baggage
    {
        $entries = $this->entries;
        $entries[$key] = new API\Entry($value, $metadata);

        return new self($entries);
    }

    /** @inheritDoc */
    public function remove(string $key): void
    {
        unset($this->entries[$key]);
    }

    public function clear(): void
    {
        $this->entries = [];
    }
}
