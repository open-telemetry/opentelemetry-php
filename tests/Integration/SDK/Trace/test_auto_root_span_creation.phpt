--TEST--
Auto root span creation
--ENV--
OTEL_PHP_AUTOLOAD_ENABLED=true
OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN=true
OTEL_TRACES_EXPORTER=console
OTEL_METRICS_EXPORTER=none
OTEL_LOGS_EXPORTER=console
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
?>
--EXPECTF--
[
    {
        "name": "GET",
        "context": {
            "trace_id": "ff000000000000000000000000000041",
            "span_id": "%s",
            "trace_state": "",
            "trace_flags": 1
        },
        "resource": {%A
        },
        "parent_span_id": "ff00000000000041",
        "kind": "KIND_SERVER",
        "start": 1721706151242976,
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
