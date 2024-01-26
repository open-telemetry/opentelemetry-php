[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/exporter-otlp/releases)
[![Source](https://img.shields.io/badge/source-exporter--otlp-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/Contrib/Otlp)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:exporter--otlp-blue)](https://github.com/opentelemetry-php/exporter-otlp)
[![Latest Version](http://poser.pugx.org/open-telemetry/exporter-otlp/v/unstable)](https://packagist.org/packages/open-telemetry/exporter-otlp/)
[![Stable](http://poser.pugx.org/open-telemetry/exporter-otlp/v/stable)](https://packagist.org/packages/open-telemetry/exporter-otlp/)

# OpenTelemetry OTLP exporter

## Documentation

https://opentelemetry.io/docs/instrumentation/php/exporters/#otlp

## Usage

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/exporters/otlp_http.php

## Http transport

```php
$transport = (new \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory())->create('http://collector:4318', 'application/json');
$exporter = new \OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
```

## gRPC transport

To export over gRPC, you will need to additionally install the `open-telemetry/transport-grpc` package.

## Protobuf Runtime library

OTLP exporting requires a [protobuf implementation](https://github.com/protocolbuffers/protobuf/tree/main/php).

The `open-telemetry/gen-otlp-protobuf` requires the `google/protobuf` native implementation. It's fine for development, but
not recommended for production usage.

The recommended option for production is to install the Protobuf C extension for PHP. The extension
makes exporting _significantly_ more performant. This can be easily installed with the following command:

```shell
pecl install protobuf
```

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
