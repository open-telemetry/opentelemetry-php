--TEST--
WithSpan uses the span name passed as a named argument
--SKIPIF--
<?php if (!extension_loaded('opentelemetry') || phpversion('opentelemetry') < '1.1.0') die('WithSpan requires ext-opentelemetry >= 1.1.0'); ?>
--INI--
opentelemetry.attr_hooks_enabled = On
--ENV--
OTEL_PHP_AUTOLOAD_ENABLED=true
OTEL_TRACES_EXPORTER=console
OTEL_METRICS_EXPORTER=none
OTEL_LOGS_EXPORTER=none
OTEL_PHP_DETECTORS=none
--FILE--
<?php
require_once 'vendor/autoload.php';

use OpenTelemetry\API\Instrumentation\WithSpan;

#[WithSpan(span_name: 'custom-name')]
function foo(): void
{
}

foo();
?>
--EXPECTF--
[
    {
        "name": "custom-name",
        "context": {
            "trace_id": "%s",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 3
        },
        "resource": {%A},
        "parent_span_id": "",
        "kind": "KIND_INTERNAL",
        "start": %d,
        "end": %d,
        "attributes": {
            "code.function": "foo",
            "code.filepath": "Standard input code",
            "code.lineno": %d
        },
        "status": {
            "code": "Unset",
            "description": ""
        },
        "events": [],
        "links": [],
        "schema_url": "https:\/\/opentelemetry.io\/schemas\/%d.%d.%d"
    }
]
