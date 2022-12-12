<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

use OpenTelemetry\API\Baggage as API;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#operations
 */
interface BaggageInterface extends ImplicitContextKeyedInterface
{
    /**
     * Returns the {@see API\BaggageInterface} from the provided *$context*,
     * falling back on {@see API\BaggageInterface::getEmpty()} if there is no baggage in the provided context.
     */
    public static function fromContext(ContextInterface $context): API\BaggageInterface;

    /**
     * Returns a new empty {@see API\BaggageBuilderInterface}.
     */
    public static function getBuilder(): API\BaggageBuilderInterface;

    /**
     * Returns the current {@see Baggage} from the current {@see ContextInterface},
     * falling back on {@see API\BaggageInterface::getEmpty()} if there is no baggage in the current context.
     */
    public static function getCurrent(): API\BaggageInterface;

    /**
     * Returns a new {@see API\BaggageInterface} with no entries.
     */
    public static function getEmpty(): API\BaggageInterface;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#get-value
     */
    public function getEntry(string $key): ?API\Entry;

    /**
     * Returns the value from the {@see API\Entry} with the provided *key*.
     * @see getEntry
     *
     * @return mixed
     */
    public function getValue(string $key);

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#get-all-values
     */
    public function getAll(): iterable;

    public function isEmpty(): bool;

    /**
     * Returns a new {@see API\BaggageBuilderInterface} pre-initialized with the contents of `$this`.
     */
    public function toBuilder(): API\BaggageBuilderInterface;
}
