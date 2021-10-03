<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Baggage;

use OpenTelemetry\API\Baggage as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Scope;
use OpenTelemetry\SDK\Trace\BaggageContextKey;

/**
 * @todo Implement this in the API layer
 */
final class Baggage implements API\BaggageInterface
{
    /** @var self|null */
    private static $emptyBaggage;

    /** @inheritDoc */
    public static function fromContext(Context $context): API\BaggageInterface
    {
        if ($baggage = $context->get(BaggageContextKey::instance())) {
            return $baggage;
        }

        return self::getEmpty();
    }

    /** @inheritDoc */
    public static function getBuilder(): API\BaggageBuilderInterface
    {
        return new BaggageBuilder();
    }

    /** @inheritDoc */
    public static function getCurrent(): API\BaggageInterface
    {
        return self::fromContext(Context::getCurrent());
    }

    /** @inheritDoc */
    public static function getEmpty(): API\BaggageInterface
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
    public function getEntry(string $key): ?API\Entry
    {
        return $this->entries[$key] ?? null;
    }

    /** @inheritDoc */
    public function getValue(string $key)
    {
        if ($entry = $this->getEntry($key)) {
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
    public function toBuilder(): API\BaggageBuilderInterface
    {
        return new BaggageBuilder($this->entries);
    }

    /** @inheritDoc */
    public function storeInContext(Context $context): Context
    {
        return $context->with(BaggageContextKey::instance(), $this);
    }
}
