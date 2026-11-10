--TEST--
Test mutating a span during OnEnding
--DESCRIPTION--
Changes made in OnEnding are visible to OnEnd of later span processors.
--FILE--
<?php
require_once 'vendor/autoload.php';

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\ExtendedSpanProcessorInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\Context\ContextInterface;

$tracerProvider = (new TracerProviderBuilder())
    ->setResource(ResourceInfo::create(Attributes::create([])))
    ->addSpanProcessor(new class implements ExtendedSpanProcessorInterface {
        #[\Override]
        public function onEnding(ReadWriteSpanInterface $span): void {
            $span->updateName('updated');
            $span->setAttribute('new-key', 'new-value');
            $span->addEvent('new-event');
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
    ->addSpanProcessor(new SimpleSpanProcessor((new ConsoleSpanExporterFactory())->create()))
    ->build();

$span = $tracerProvider->getTracer('test')->spanBuilder('span')->startSpan();
$span->end();
?>
--EXPECTF--
[
    {
        "name": "updated",
        "context": {
            "trace_id": "%s",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 3
        },
        "resource": [],
        "parent_span_id": "",
        "kind": "KIND_INTERNAL",
        "start": %d,
        "end": %d,
        "attributes": {
            "new-key": "new-value"
        },
        "status": {
            "code": "Unset",
            "description": ""
        },
        "events": [
            {
                "name": "new-event",
                "timestamp": %d,
                "attributes": []
            }
        ],
        "links": [],
        "schema_url": ""
    }
]
