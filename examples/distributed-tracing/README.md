# Distributed Tracing Example
This example uses `docker-compose`, and illustrates the distributed tracing functionality of OpenTelemetry. An HTTP request to service-one will make multiple con current HTTP requests, each of which is injected with a `traceparent` header.

All trace data is exported via grpc to an [OpenTelemetry Collector](https://opentelemetry.io/docs/collector/), where they are forwarded to zipkin and jaeger.

The example is presented as a [slim framework](https://www.slimframework.com/) single-page application for simplicity, and uses Guzzle as an HTTP client. The same application source is used for all services.

## Running the example
```bash
$ docker-compose run service-one composer update
$ docker-compose up
# in a separate terminal
$ curl localhost:8000/users/otel
```
## Notes
* A guzzle middleware is responsible for wrapping each outgoing HTTP request in a span with [http-based attributes](https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/semantic_conventions/http.md), and injecting `traceparent` (and optionally `tracestate`) headers.
* A slim middleware is responsible for starting the root span, using the route pattern for the span name due to its low cardinality (see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/api.md#span). This is also where incoming trace headers are managed.
