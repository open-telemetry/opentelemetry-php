[![Source](https://img.shields.io/badge/source-transport--grpc-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/Contrib/Grpc)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:transport--grpc-blue)](https://github.com/opentelemetry-php/transport-grpc)
[![Latest Version](http://poser.pugx.org/open-telemetry/transport-grpc/v/unstable)](https://packagist.org/packages/open-telemetry/transport-grpc/)
[![Stable](http://poser.pugx.org/open-telemetry/transport-grpc/v/stable)](https://packagist.org/packages/open-telemetry/transport-grpc/)


# OpenTelemetry gRPC Transport

gRPC transport for OpenTelemetry.

This package provides a transport which can be used by `open-telemetry/exporter-otlp` to send protobuf-encoded telemetry
over gRPC.

## Documentation

https://opentelemetry.io/docs/instrumentation/php/exporters/#otlp

## Usage

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/exporters/otlp_grpc.php

```php
$transport = (new \OpenTelemetry\Contrib\Grpc\GrpcTransportFactory())->create('http://collector:4317');
$exporter = new \OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
```
