<?php

declare(strict_types=1);

namespace OpenTelemetry\Baggage;

use OpenTelemetry\Baggage as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ImplicitContextKeyed;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#operations
 */
interface Baggage extends ImplicitContextKeyed
{
    /**
     * Returns the {@see API\Baggage} from the provided *$context*,
     * falling back on {@see API\Baggage::getEmpty()} if there is no baggage in the provided context.
     *
     * @todo Implement this in the API layer
     */
    public static function fromContext(Context $context): API\Baggage;

    /**
     * Returns a new empty {@see API\BaggageBuilder}.
     */
    public static function getBuilder(): API\BaggageBuilder;

    /**
     * Returns the current {@see Baggage} from the current {@see Context},
     * falling back on {@see API\Baggage::getEmpty()} if there is no baggage in the current context.
     *
     * @todo Implement this in the API layer
     */
    public static function getCurrent(): API\Baggage;

    /**
     * Returns a new {@see API\Baggage} with no entries.
     */
    public static function getEmpty(): API\Baggage;

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

    /**
     * Returns a new {@see API\BaggageBuilder} pre-initialized with the contents of `$this`.
     */
    public function toBuilder(): API\BaggageBuilder;
}
