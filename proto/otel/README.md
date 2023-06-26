[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/gen-otlp-protobuf/releases)
[![Source](https://img.shields.io/badge/source-gen--otlp--protobuf-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/proto/otel)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:gen--otlp--protobuf-blue)](https://github.com/opentelemetry-php/gen-otlp-protobuf)
[![Latest Version](http://poser.pugx.org/open-telemetry/gen-otlp-protobuf/v/unstable)](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf/)
[![Stable](http://poser.pugx.org/open-telemetry/gen-otlp-protobuf/v/stable)](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf/)

# OpenTelemetry protobuf files

## Protobuf Runtime library

There exist two protobuf runtime libraries that offer the same set of APIs, allowing developers to choose the one that 
best suits their needs.

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
