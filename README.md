# OpenTelemetry for PHP

See [opentelemetry.io](https://opentelemetry.io/docs/instrumentation/php/) for more information and documentation.

![CI Build](https://github.com/open-telemetry/opentelemetry-php/workflows/PHP%20QA/badge.svg)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)
[![Slack](https://img.shields.io/badge/slack-@cncf/otel--php-brightgreen.svg?logo=slack)](https://cloud-native.slack.com/archives/D03FAB6GN0K)

<details>
<summary>Table of Contents</summary>

<!-- toc -->

- [Introduction](#introduction)
  - [Releases](#releases)
- [Getting started](#getting-started)
- [Project status](#project-status)
  - [Specification conformance](#specification-conformance)
  - [Backwards compatibility](#backwards-compatibility)
- [Getting started](#getting-started)
  - [Instrumenting an application](#using-opentelemetry-in-an-application)
  - [Instrumenting a library](#using-opentelemetry-to-instrument-a-library)
  - [Trace signals](#trace-signals)
    - [Auto-instrumentation](#auto-instrumentation)
    - [Framework instrumentation](#framework-instrumentation)
    - [Manual instrumentation](#manual-instrumentation)
    - [Distributed tracing](#distributed-tracing)
    - [Examples](#trace-examples)
  - [Metrics signals](#metrics-signals)
    - [Examples](#metrics-examples)
  - [Log signals](#log-signals)
- [Versioning](#versioning)
- [Contributing](#contributing)
<!-- tocstop -->

</details>

# Introduction

This is the **[monorepo](https://en.wikipedia.org/wiki/Monorepo)** for the **main** components of [OpenTelemetry](https://opentelemetry.io/) for PHP.

## Releases

Releases for both this repository and [contrib](https://github.com/open-telemetry/opentelemetry-php-contrib) are
based on read-only [git subtree splits](https://github.com/splitsh/lite) from our monorepo. You should refer to
[packagist.org](https://packagist.org/packages/open-telemetry/) for all packages, their versions and details.

You can also look at the read-only repositories, which live in the
[opentelemetry-php](https://github.com/opentelemetry-php) organization.

# Getting Started

See [Getting Started](https://opentelemetry.io/docs/instrumentation/php/getting-started/)

All OpenTelemetry libraries are distributed via packagist, notably:

- API: [open-telemetry/api](https://packagist.org/packages/open-telemetry/api)
- SDK: [open-telemetry/sdk](https://packagist.org/packages/open-telemetry/sdk)
- Context: [open-telemetry/context](https://packagist.org/packages/open-telemetry/context)
- Semantic Conventions: [open-telemetry/sem-conv](https://packagist.org/packages/open-telemetry/sem-conv)
- Exporters: [open-telemetry/exporter-*](https://packagist.org/search/?query=open-telemetry&tags=exporter)
- Extensions: [open-telemetry/extension-*](https://packagist.org/search/?query=open-telemetry&tags=extension)
- Auto-instrumentation modules: [open-telemetry/opentelemetry-auto-*](https://packagist.org/search/?query=open-telemetry&tags=instrumentation)

The [open-telemetry/opentelemetry-php-instrumentation](https://github.com/open-telemetry/opentelemetry-php-instrumentation) extension can be
installed to enable auto-instrumentation of PHP code (in conjunction with contrib modules).

The [OpenTelemetry PHP Contrib repository](https://github.com/open-telemetry/opentelemetry-php-contrib/) hosts contributions that are not part of the core
distribution or components of the library.

## Specification conformance
We attempt to keep the [OpenTelemetry Specification Matrix](https://github.com/open-telemetry/opentelemetry-specification/blob/master/spec-compliance-matrix.md) up to date in order to show which features are available and which have not yet been implemented.

If you find an inconsistency in the data in the matrix, please let us know in our slack channel and we'll get it rectified.

## Backwards compatibility

See [compatibility readme](src/SDK/Common/Dev/Compatibility/README.md).

# Requirements

See https://opentelemetry.io/docs/php/getting-started#requirements

## Using OpenTelemetry in an Application

Your application should only depend on Interfaces provided by the API package:

```bash
$ composer require open-telemetry/api
```
In the best case you will use [Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle)
and write an adapter to not depend on the API directly.

Make sure your application works with a dependency on the API only, however to make full use of the library
you want to install the **SDK** package and an exporter from the **Contrib** packages as well:

```bash
$ composer require open-telemetry/sdk
```
or
```bash
$ composer require open-telemetry/sdk open-telemetry/exporter-zipkin
```
Make sure any **SDK** or **Contrib** code is set up by your configuration, bootstrap, dependency injection, etc.

## Using OpenTelemetry to instrument a Library

Your library should only depend on Interfaces provided by the API package:

```bash
$ composer require open-telemetry/api
```

For development and testing purposes you also want to install **SDK** and **Contrib** packages:
```bash
$ composer require --dev open-telemetry/sdk open-telemetry/sdk-contrib
```

## SDK autoloading

See https://opentelemetry.io/docs/instrumentation/php/sdk#autoloading

See [autoload_sdk.php example](./examples/autoload_sdk.php)

## Configuration

See https://opentelemetry.io/docs/instrumentation/php/sdk#configuration

## Trace signals

### Auto-instrumentation

See https://opentelemetry.io/docs/instrumentation/php/automatic/

### Framework instrumentation

* [Symfony SDK Bundle](https://github.com/open-telemetry/opentelemetry-php-contrib/tree/main/src/Symfony/OtelSdkBundle) is the recommended way to use opentelemetry-php with symfony

### Distributed tracing

See https://opentelemetry.io/docs/instrumentation/php/propagation/

See [examples/traces/demo](examples/traces/demo) for a working example.

### Trace examples

You can use the [zipkin](/examples/traces/exporters/zipkin.php) example to test out the reference
implementations. This example performs a sample trace with a grouping of 5 spans and exports the result
to a local zipkin or jaeger instance.

If you'd like a no-fuss way to test this out with docker and docker-compose, you can perform the following simple steps:

1) Install the necessary dependencies by running `make install`.
2) Execute the example trace using `make smoke-test-exporter-examples:`.

Exported spans can be seen in zipkin at [http://127.0.0.1:9411](http://127.0.0.1:9411)

Exported spans can also be seen in jaeger at [http://127.0.0.1:16686](http://127.0.0.1:16686)

## Metrics signals

Meters must be obtained from a `MeterProvider`

### Metrics examples

See [basic example](./examples/metrics/basic.php)

## Log signals

See https://opentelemetry.io/docs/instrumentation/php/logging/

### Logging examples

See [getting started example](./examples/logs/getting_started.php)

# Versioning

Versioning rationale can be found in the [Versioning Documentation](/docs/versioning.md)

# Contributing

We would love to have you on board, please see our [Development README](./DEVELOPMENT.md) and [Contributing README](./CONTRIBUTING.md).
