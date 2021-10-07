<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;

class NoopSpanProcessor implements SpanProcessorInterface
{
    private static ?SpanProcessorInterface $instance = null;

    public static function getInstance(): SpanProcessorInterface
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** @inheritDoc */
    public function onStart(ReadWriteSpanInterface $span, ?Context $parentContext = null): void
    {
    }

    /** @inheritDoc */
    public function onEnd(ReadableSpanInterface $span): void
    {
    }

    /** @inheritDoc */
    public function forceFlush(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public function shutdown(): bool
    {
        return $this->forceFlush();
    }
}
