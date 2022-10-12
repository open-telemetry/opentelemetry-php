<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
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
    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void
    {
    } //@codeCoverageIgnore

    /** @inheritDoc */
    public function onEnd(ReadableSpanInterface $span): void
    {
    } //@codeCoverageIgnore

    /** @inheritDoc */
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    /** @inheritDoc */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->forceFlush();
    }
}
