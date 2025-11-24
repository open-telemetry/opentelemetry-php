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

$exporter = (new \OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory())->create();

$processor = new class($exporter) extends ExtendedSpanProcessorInterface {
    #[\Override]
    public function onEnding(ReadWriteSpanInterface $span): void {
        $span->updateName('updated');
        $span->setAttribute('new-key', 'new-value');
        $span->addEvent('new-event');
    }
};

$tracerProvider = (new TracerProviderBuilder())
    ->addSpanProcessor($processor)
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
            "trace_flags": 1
        },
        "resource": {
            "telemetry.sdk.name": "opentelemetry",
            "telemetry.sdk.language": "php",
            "telemetry.sdk.version": "%s",
            "telemetry.distro.name": "opentelemetry-php-instrumentation",
            "telemetry.distro.version": "%s",
            "service.name": "%s"
        },
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
]'
