<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/**
 * TraceState parses and stores the tracestate header as an immutable list of string
 * key/value pairs. It provides the following operations following the rules described
 * in the W3C Trace Context specification:
 *      - Get value for a given key
 *      - Add a new key/value pair
 *      - Update an existing value for a given key
 *      - Delete a key/value pair
 *
 * All mutating operations return a new TraceState with the modifications applied.
 *
 * @see https://www.w3.org/TR/trace-context/#tracestate-header
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/trace/api.md#tracestate
 */
interface TraceStateInterface
{
    /**
     * Return a new TraceState object that inherits from this TraceState
     * and contains the given key value pair.
     *
     * @param string $key
     * @param string $value
     * @return TraceStateInterface
     */
    public function with(string $key, string $value): TraceStateInterface;

    /**
     * Return a new TraceState object that inherits from this TraceState
     * without the given key value pair.
     *
     * @param string $key
     * @return TraceStateInterface
     */
    public function without(string $key): TraceStateInterface;

    /**
     * Return the value of a given key from this TraceState if it exists
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string;

    /**
     * Get the list-member count in this TraceState
     *
     * @return int
     */
    public function getListMemberCount(): int;

    /**
     * Returns a string representation of this TraceSate
     */
    public function __toString(): string;
}
