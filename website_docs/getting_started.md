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

use OpenTelemetry\SDK\Trace\SpanProcessor\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
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
    $span1->activate();
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
        "trace_id": "e7bc999fb17f453c6e6445802ba1e558",
        "span_id": "24afe9c453481636",
        "trace_state": null
    },
    "parent_span_id": "c63030cc93c48641",
    "kind": "KIND_INTERNAL",
    "start": 1635373538696880128,
    "end": 1635373538697000960,
    "attributes": [],
    "status": {
        "code": "Unset",
        "description": ""
    },
    "events": []
}
{
    "name": "foo",
    "context": {
        "trace_id": "e7bc999fb17f453c6e6445802ba1e558",
        "span_id": "c63030cc93c48641",
        "trace_state": null
    },
    "parent_span_id": "4e6396224842fc15",
    "kind": "KIND_INTERNAL",
    "start": 1635373538696482048,
    "end": 1635373538700564992,
    "attributes": [],
    "status": {
        "code": "Unset",
        "description": ""
    },
    "events": []
}
{
    "name": "root",
    "context": {
        "trace_id": "e7bc999fb17f453c6e6445802ba1e558",
        "span_id": "4e6396224842fc15",
        "trace_state": null
    },
    "parent_span_id": "",
    "kind": "KIND_INTERNAL",
    "start": 1635373538691308032,
    "end": 1635373538700800000,
    "attributes": [],
    "status": {
        "code": "Unset",
        "description": ""
    },
    "events": []
}
```



