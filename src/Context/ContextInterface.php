<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

interface ContextInterface
{
    /**
     * @param non-empty-string $key
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/context/context.md#create-a-key
     */
    public static function createKey(string $key): ContextKeyInterface;

    public static function getCurrent(): ContextInterface;

    /**
     * Makes `$this` the currently active {@see ContextInterface}.
     */
    public function activate(): ScopeInterface;

    /**
     * This adds a key/value pair to this Context.
     *
     * @psalm-template T
     * @psalm-param ContextKeyInterface<T> $key
     * @psalm-param T|null $value
     */
    public function with(ContextKeyInterface $key, $value): ContextInterface;

    public function withContextValue(ImplicitContextKeyedInterface $value): ContextInterface;

    /**
     * Fetch a value from the Context given a key value.
     *
     * @psalm-template T
     * @psalm-param ContextKeyInterface<T> $key
     * @psalm-return T|null
     */
    public function get(ContextKeyInterface $key);
}
