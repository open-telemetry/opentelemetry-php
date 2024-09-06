--TEST--
Auto root span creation
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

class TestClass
{
    #[WithSpan]
    public static function bar(): void
    {
        self::baz();
    }
    #[WithSpan]
    private static function baz(): void
    {
        //do nothing
    }
}

#[WithSpan]
function foo(): void
{
    var_dump('foo::start');
    TestClass::bar();
    var_dump('foo::end');
}

foo();
?>
--EXPECTF--
%A
string(10) "foo::start"
string(8) "foo::end"
[
    {
        "name": "TestClass::baz",
        "context": {
            "trace_id": "%s",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 1
        },
        "resource": [],
        "parent_span_id": "%s",
        "kind": "KIND_INTERNAL",
        "start": %d,
        "end": %d,
        "attributes": {
            "code.function": "baz",
            "code.namespace": "TestClass",
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
[
    {
        "name": "TestClass::bar",
        "context": {
            "trace_id": "%s",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 1
        },
        "resource": [],
        "parent_span_id": "%s",
        "kind": "KIND_INTERNAL",
        "start": %d,
        "end": %d,
        "attributes": {
            "code.function": "bar",
            "code.namespace": "TestClass",
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
[
    {
        "name": "foo",
        "context": {
            "trace_id": "%s",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 1
        },
        "resource": [],
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
