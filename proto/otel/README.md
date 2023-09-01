[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/gen-otlp-protobuf/releases)
[![Source](https://img.shields.io/badge/source-gen--otlp--protobuf-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/proto/otel)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:gen--otlp--protobuf-blue)](https://github.com/opentelemetry-php/gen-otlp-protobuf)
[![Latest Version](http://poser.pugx.org/open-telemetry/gen-otlp-protobuf/v/unstable)](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf/)
[![Stable](http://poser.pugx.org/open-telemetry/gen-otlp-protobuf/v/stable)](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf/)

# OpenTelemetry protobuf files

## Protobuf Runtime library

OTLP exporting requires a [protobuf runtime library](https://github.com/protocolbuffers/protobuf/tree/main/php).

There exist two protobuf runtime libraries that offer the same set of APIs, allowing developers to choose the one that
best suits their needs.

This package requires `google/protobuf`, which is the native implementation. It is easy to install and a good way to get
started quickly.

Alternatively, and the recommended option for production is to install the Protobuf C extension for PHP. The extension
makes OTLP exporting _significantly_ more performant. The extension can be installed with the following command:

```shell
pecl install protobuf
```

The extension can be installed alongside the native library, and it will be used instead if available.

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
