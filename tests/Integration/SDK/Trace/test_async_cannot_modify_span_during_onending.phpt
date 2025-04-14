--TEST--
Test that async code cannot update span during OnEnding
--DESCRIPTION--
The SDK MUST guarantee that the span can no longer be modified by any other thread before invoking OnEnding of the first SpanProcessor.
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
            $spanName = $span->getName();

            Amp\delay(0);
            assert($span->getName() === $spanName);
        }

        public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void {}
        public function onEnd(ReadableSpanInterface $span): void {}
        public function forceFlush(?CancellationInterface $cancellation = null): bool { return true; }
        public function shutdown(?CancellationInterface $cancellation = null): bool { return true; }
    })
    ->build();

$span = $tracerProvider->getTracer('test')->spanBuilder('span')->startSpan();
Amp\async($span->updateName(...), 'should-not-update');
$span->end();
?>
--EXPECT--
