# OpenTelemetry gRPC Transport

gRPC transport for OpenTelemetry.

## Usage

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/exporters/otlp_grpc.php

```php
$transport = (new \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory())->create('http://collector:4317');
$exporter = new \OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
```
