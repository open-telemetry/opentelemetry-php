--TEST--
Test ending a span during OnEnding
--FILE--
<?php
require_once 'vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\Context\ContextInterface;

$tracerProvider = (new TracerProviderBuilder())
    ->addSpanProcessor(new class implements SpanProcessorInterface {

        public function onEnding(ReadWriteSpanInterface $span): void {
            $span->end();
        }

        public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void {}
        public function onEnd(ReadableSpanInterface $span): void {}
        public function forceFlush(?CancellationInterface $cancellation = null): bool { return true; }
        public function shutdown(?CancellationInterface $cancellation = null): bool { return true; }
    })
    ->build();

$span = $tracerProvider->getTracer('test')->spanBuilder('span')->startSpan();
$span->end();
?>
--EXPECT--
