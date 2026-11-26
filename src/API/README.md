[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/api/releases)
[![Source](https://img.shields.io/badge/source-api-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/API)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:api-blue)](https://github.com/opentelemetry-php/api)
[![Latest Version](http://poser.pugx.org/open-telemetry/api/v/unstable)](https://packagist.org/packages/open-telemetry/api/)
[![Stable](http://poser.pugx.org/open-telemetry/api/v/stable)](https://packagist.org/packages/open-telemetry/api/)

# OpenTelemetry API

Documentation: https://opentelemetry.io/docs/instrumentation/php

## Read Globals inside a hook, not an autoload file

`Globals` builds the SDK on first use and keeps the result for the request. A Composer autoload
file can run before the SDK autoloader's, and a `Globals` call made there leaves every global noop.
Register instrumentation through the `Instrumentation` SPI, or call `Globals` inside a hook.

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
