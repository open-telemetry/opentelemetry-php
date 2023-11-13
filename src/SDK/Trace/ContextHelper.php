<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;
use OpenTelemetry\Context\ContextKeys;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\Context\ExecutionContextAwareInterface;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;
use OpenTelemetry\Context\ScopeInterface;

/**
 * SDK helper classes for dealing with Context
 */
final class ContextHelper
{
    /**
     * Function to remove any contexts that may have been left over by early exits where
     * scope->detach was not called.
     * @param callable $fn A function that gets called with the expected context.
     *                     Commonly used to end spans e.g.
     *                     function($ctx) {
     *                         $span = Span::fromContext($ctx->current());
     *                         $span->end();
     *                     }
     * @return void
     */
    public static function cleanup(callable $fn): void
    {
        $ctx = Context::storage();
        while ($ctx->current() !== Context::getRoot()) {
            $fn($ctx);
            $ds = new \OpenTelemetry\Context\DebugScope($ctx->scope());
            $ds->detach();
        }
    }
}
