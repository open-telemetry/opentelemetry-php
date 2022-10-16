# OpenTelemetry OTLP exporter

## Usage

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/features/exporters/otlp_http.php

## Http transport

To export over HTTP, you will need to provide implementations for [http factory implementation](https://packagist.org/providers/psr/http-factory-implementation) and [async-client-implementation](https://packagist.org/providers/php-http/async-client-implementation).

You will also need to install [php-http/discovery](https://packagist.org/packages/php-http/discovery).

```php
$transport = (new \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory())->create('http://collector:4318');
$exporter = new \OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
```

## gRPC transport

To export over gRPC, you will need to additionally install the `open-telemetry/exporter-grpc` package.
