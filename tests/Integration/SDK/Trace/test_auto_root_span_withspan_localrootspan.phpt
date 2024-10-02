--TEST--
Tests that auto root span, withspan auto-instrumentation and local root span all work together
--SKIPIF--
<?php if (!extension_loaded('opentelemetry') || phpversion('opentelemetry') < '1.1.0') die('WithSpan requires ext-opentelemetry >= 1.1.0'); ?>
--INI--
opentelemetry.attr_hooks_enabled = On
opentelemetry.attr_pre_handler_function = OpenTelemetry\API\Instrumentation\WithSpanHandler::pre
opentelemetry.attr_post_handler_function = OpenTelemetry\API\Instrumentation\WithSpanHandler::post
--ENV--
OTEL_PHP_AUTOLOAD_ENABLED=true
OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN=true
OTEL_TRACES_EXPORTER=console
OTEL_METRICS_EXPORTER=none
OTEL_LOGS_EXPORTER=none
OTEL_PHP_DETECTORS=none
REQUEST_METHOD=GET
REQUEST_URI=/foo?bar=baz
REQUEST_SCHEME=https
SERVER_NAME=example.com
SERVER_PORT=8080
HTTP_HOST=example.com:8080
HTTP_USER_AGENT=my-user-agent/1.0
REQUEST_TIME_FLOAT=1721706151.242976
HTTP_TRACEPARENT=00-ff000000000000000000000000000041-ff00000000000041-01
--FILE--
<?php
require_once 'vendor/autoload.php';

use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\SpanAttribute;

$root = \OpenTelemetry\API\Trace\LocalRootSpan::current();
$root->updateName('GET updated-name');

#[WithSpan]
function foo(
    #[SpanAttribute] string $word
): void
{
    //do nothing
}
//"word" -> "bar" should appear as a span attribute
foo('bar');
?>
--EXPECTF--
%A
[
    {
        "name": "foo",
        "context": {
            "trace_id": "ff000000000000000000000000000041",
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
            "code.function": "foo",
            "code.filepath": "%s",
            "code.lineno": %d,
            "word": "bar"
        },
        "status": {
            "code": "Unset",
            "description": ""
        },
        "events": [],
        "links": [],
        "schema_url": "%s"
    }
]
[
    {
        "name": "GET updated-name",
        "context": {
            "trace_id": "ff000000000000000000000000000041",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 1
        },
        "resource": [],
        "parent_span_id": "ff00000000000041",
        "kind": "KIND_SERVER",
        "start": %d,
        "end": %d,
        "attributes": {
            "url.full": "%s",
            "http.request.method": "GET",
            "http.request.body.size": "",
            "user_agent.original": "my-user-agent\/1.0",
            "server.address": "%S",
            "server.port": %d,
            "url.scheme": "https",
            "url.path": "\/foo"
        },
        "status": {
            "code": "Unset",
            "description": ""
        },
        "events": [],
        "links": [],
        "schema_url": "%s"
    }
]
