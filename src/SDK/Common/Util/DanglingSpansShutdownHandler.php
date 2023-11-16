<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\DebugScope;

/**
 * SDK helper classes for dealing with dangling spans
 */
final class DanglingSpansShutdownHandler
{
    /**
     * Function to remove any contexts that may have been left over by early exits where
     * scope->detach was not called. e.g. An early exit() call from an PHP script/request.
     *
     * To register this call:
     * ShutdownHandler::register(DanglingSpansShutdownHandler::shutdown)
     *
     * BUT make sure you do this before you register the other shutdown handlers or any spans won't be processed/exported
     *
     * @return void
     */
    public static function shutdown(): void
    {
        $ctx = Context::storage();
        while ($ctx->current() !== Context::getRoot()) {
            $span = Span::fromContext($ctx->current());
            $span->end();
            $ds = new DebugScope($ctx->scope());
            $ds->detach();
        }
    }
}
