--TEST--
Test ending a span during OnEnding
--FILE--
<?php
require_once 'vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ExtendedSpanProcessorInterface;
use OpenTelemetry\Context\ContextInterface;

$tracerProvider = (new TracerProviderBuilder())
    ->addSpanProcessor(new class implements ExtendedSpanProcessorInterface {
        #[\Override]
        public function onEnding(ReadWriteSpanInterface $span): void {
            $span->end();
        }
        #[\Override]
        public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void {}
        #[\Override]
        public function onEnd(ReadableSpanInterface $span): void {}
        #[\Override]
        public function forceFlush(?CancellationInterface $cancellation = null): bool { return true; }
        #[\Override]
        public function shutdown(?CancellationInterface $cancellation = null): bool { return true; }
    })
    ->build();

$span = $tracerProvider->getTracer('test')->spanBuilder('span')->startSpan();
$span->end();
?>
--EXPECT--
