<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * Immutable execution scoped propagation mechanism.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#context
 */
interface ContextInterface
{
    /**
     * Creates a new context key.
     *
     * @param non-empty-string $key name of the key
     * @return ContextKeyInterface created key
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#create-a-key
     */
    public static function createKey(string $key): ContextKeyInterface;

    /**
     * Returns the current context.
     *
     * @return ContextInterface current context
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#get-current-context
     */
    public static function getCurrent(): ContextInterface;

    /**
     * Attaches this context as active context.
     *
     * The returned scope has to be {@link ScopeInterface::detach()}ed. In most
     * cases this should be done using a `try-finally` statement:
     * ```php
     * $scope = $context->activate();
     * try {
     *     // ...
     * } finally {
     *     $scope->detach();
     * }
     * ```
     *
     * @return ScopeInterface scope to detach the context and restore the previous
     *         context
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#attach-context
     */
    public function activate(): ScopeInterface;

    /**
     * Returns a context with the given key set to the given value.
     *
     * @template T
     * @param ContextKeyInterface<T> $key key to set
     * @param T|null $value value to set
     * @return ContextInterface a context with the given key set to `$value`
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#set-value
     */
    public function with(ContextKeyInterface $key, $value): ContextInterface;

    /**
     * Returns a context with the given value set.
     *
     * @param ImplicitContextKeyedInterface $value value to set
     * @return ContextInterface a context with the given `$value`
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#set-value
     */
    public function withContextValue(ImplicitContextKeyedInterface $value): ContextInterface;

    /**
     * Returns the value assigned to the given key.
     *
     * @template T
     * @param ContextKeyInterface<T> $key key to get
     * @return T|null value assigned to `$key`, or null if no such value exists
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#get-value
     */
    public function get(ContextKeyInterface $key);
}
