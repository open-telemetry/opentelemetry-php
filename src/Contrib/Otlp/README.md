# OpenTelemetry OTLP exporter

## Usage

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/exporters/otlp_http.php

## Http transport

To export over HTTP, you will need to provide implementations for [http factory implementation](https://packagist.org/providers/psr/http-factory-implementation) and [async-client-implementation](https://packagist.org/providers/php-http/async-client-implementation).

You will also need to install [php-http/discovery](https://packagist.org/packages/php-http/discovery).

```php
$transport = (new \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory())->create('http://collector:4318');
$exporter = new \OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
```

## gRPC transport

To export over gRPC, you will need to additionally install the `open-telemetry/exporter-grpc` package.

## Protobuf Runtime library

There exist two protobuf runtime libraries that offer the same set of APIs, allowing developers to choose the one that
best suits their needs. You must install one of them, in order to use the OTLP exporter.

The first and easiest option is to install the Protobuf PHP Runtime Library through composer. This can be the easiest
way to get started quickly. Either run `composer require google/protobuf`, or update your `composer.json` as follows:

```json
"require": {
  "google/protobuf": "^v3.3.0"
}
```

Alternatively, and the recommended option for production is to install the Protobuf C extension for PHP. The extension
makes both exporters _significantly_ more performant. This can be easily installed with the following command:
```sh
$ sudo pecl install protobuf
```
