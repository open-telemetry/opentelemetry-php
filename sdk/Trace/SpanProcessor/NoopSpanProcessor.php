<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\ReadableSpan;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Trace as API;

class NoopSpanProcessor implements SpanProcessor
{
    private static ?SpanProcessor $instance = null;

    public static function getInstance(): SpanProcessor
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** @inheritDoc */
    public function onStart(API\Span $span, ?Context $parentContext = null): void
    {
    }

    /** @inheritDoc */
    public function onEnd(ReadableSpan $span): void
    {
    }

    /** @inheritDoc */
    public function forceFlush(): void
    {
    }

    /** @inheritDoc */
    public function shutdown(): void
    {
        $this->forceFlush();
    }
}
