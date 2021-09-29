# Getting Started

This guide will help you get up and running with OpenTelemetry for PHP.

## Basic Example: Exporting Traces to the console

### To being you'll need to install the OpenTelemery SDK


```bash
$ composer require open-telemetry/opentelemetry
```

This example will use the the ConsoleSpanExporter which, will print the Spans to stdout. A Span typically represents a single unit of work, a Trace is a grouping of Spans.


```php
<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';


use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\TracerProvider;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    )
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

try {
    $span1 = $tracer->spanBuilder('foo')->startSpan();
    try {
        $span2 = $tracer->spanBuilder('bar')->startSpan();
        echo 'OpenTelemetry welcomes PHP' . PHP_EOL;
    } finally {
        $span2->end();
    }
} finally {
    $span1->end();
}
$rootSpan->end();
```

Running this script should produce a similar output to this. In this example we have 3 spans within a single trace.

```bash
$ php GettingStarted.php
Starting ConsoleSpanExporter
OpenTelemetry welcomes PHP
{
    "name": "bar",
    "context": {
        "trace_id": "a83d0e1fb781490bbd0bbdc4eb9b06dd",
        "span_id": "a8f34ed2cc48e9a9",
        "trace_state": null
    },
    "parent_span_id": "a83d0e1fb781490bbd0bbdc4eb9b06dd",
    "kind": 0,
    "start": 1633643693276375040,
    "end": 1633643693276495872,
    "attributes": {},
    "status": {},
    "events": []
}
{
    "name": "foo",
    "context": {
        "trace_id": "a83d0e1fb781490bbd0bbdc4eb9b06dd",
        "span_id": "14abaae74adb8545",
        "trace_state": null
    },
    "parent_span_id": "a83d0e1fb781490bbd0bbdc4eb9b06dd",
    "kind": 0,
    "start": 1633643693275846912,
    "end": 1633643693279785984,
    "attributes": {},
    "status": {},
    "events": []
}
{
    "name": "root",
    "context": {
        "trace_id": "a83d0e1fb781490bbd0bbdc4eb9b06dd",
        "span_id": "7b4df1ad8849c7f1",
        "trace_state": null
    },
    "parent_span_id": "",
    "kind": 0,
    "start": 1633643693269499904,
    "end": 1633643693279921920,
    "attributes": {},
    "status": {},
    "events": []
}
```



