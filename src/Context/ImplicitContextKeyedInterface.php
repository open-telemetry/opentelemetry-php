<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * Represents a value that can be stored within {@see ContextInterface}.
 * Allows storing themselves without exposing a {@see ContextKeyInterface}.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#context-interaction
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/baggage/api.md#context-interaction
 */
interface ImplicitContextKeyedInterface
{
    /**
     * Adds `$this` to the {@see Context::getCurrent() current context} and makes
     * the new {@see Context} the current context.
     *
     * {@see ScopeInterface::detach()} _MUST_ be called to properly restore the previous context.
     *
     * This method is equivalent to `Context::getCurrent().with($value).activate();`.
     *
     * @todo: Update this to suggest using the new helper method way to doing something in a specific context/span.
     */
    public function activate(): ScopeInterface;

    /**
     * Returns a new {@see ContextInterface} created by setting `$this` into the provided [@see ContextInterface}.
     */
    public function storeInContext(ContextInterface $context): ContextInterface;
}
