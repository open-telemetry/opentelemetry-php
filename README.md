# OpenTelemetry PHP

![CI Build](https://github.com/open-telemetry/opentelemetry-php/workflows/PHP%20QA/badge.svg)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)
[![Slack](https://img.shields.io/badge/slack-@cncf/otel--php-brightgreen.svg?logo=slack)](https://cloud-native.slack.com/archives/D03FAB6GN0K)

<details>
<summary>Table of Contents</summary>

<!-- toc -->

- [Introduction](#introduction)
- [Project status](#project-status)
  - [Specification conformance](#specification-conformance)
  - [Backwards compatibility](#backwards-compatibility)
- [Requirements](#requirements)
  - [Required dependencies](#required-dependencies)
  - [Optional dependencies](#optional-dependencies)
- [Installation](#installation)
- [Getting started](#getting-started)
  - [Instrumenting an application](#using-opentelemetry-in-an-application)
  - [Instrumenting a library](#using-opentelemetry-to-instrument-a-library)
  - [Configuration](#configuration)
  - [Trace signals](#trace-signals)
    - [Auto-instrumentation](#auto-instrumentation)
    - [Framework instrumentation](#framework-instrumentation)
    - [Manual instrumentation](#manual-instrumentation)
      - [Set up a tracer](#set-up-a-tracer)
      - [Creating spans](#creating-spans)
      - [Nesting spans](#nesting-spans)
      - [Distributed tracing](#distributed-tracing)
    - [Examples](#metrics-examples)
  - [Metrics signals](#metrics-signals)
    - [Examples](#trace-examples)
  - [Log signals](#log-signals)
- [User Quickstarts](#user-quickstarts)
- [Versioning](#versioning)
- [Contributing](#contributing)
<!-- tocstop -->

</details>

# Introduction

This is the **[monorepo](https://en.wikipedia.org/wiki/Monorepo)** for the **main** components of **[OpenTelemetry](https://opentelemetry.io/) PHP**. 

All OpenTelemetry libraries are distributed via packagist, notably:

- API: [open-telemetry/api](https://packagist.org/packages/open-telemetry/api)
- SDK: [open-telemetry/sdk](https://packagist.org/packages/open-telemetry/sdk)
- Semantic Conventions: [open-telemetry/sem-conv](https://packagist.org/packages/open-telemetry/sem-conv)
- Context: [open-telemetry/context](https://packagist.org/packages/open-telemetry/context)
- Exporters: [open-telemetry/exporter-*](https://packagist.org/search/?query=open-telemetry&tags=exporter)
- Contrib: [open-telemetry/sdk-contrib](https://packagist.org/packages/open-telemetry/sdk-contrib)
- Extensions: [open-telemetry/extension-*](https://packagist.org/search/?query=open-telemetry&tags=extension)

The [open-telemetry/opentelemetry](https://packagist.org/packages/open-telemetry/opentelemetry) package contains all of the above and is the easiest way to try out OpenTelemetry.

The [open-telemetry/opentelemetry-php-instrumentation](https://github.com/open-telemetry/opentelemetry-php-instrumentation) extension can be installed to enable auto-instrumentation of PHP code (in conjunction with contrib modules).

---
This repository also hosts and distributes generated client code used by individual components as separate packages.  These packages are:
- Generated [OTLP](https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/otlp.md) ProtoBuf files:
  [open-telemetry/gen-otlp-protobuf](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf)

For now the generated code packages are meant to be only used by library components internally.

---

The [OpenTelemetry PHP Contrib repository](https://github.com/open-telemetry/opentelemetry-php-contrib/) hosts contributions that are not part of the core
distribution or components of the library. Typically, these contributions are vendor specific receivers/exporters and/or
components that are only useful to a relatively small number of users.  

Additional packages, demos and tools are hosted or distributed in the [OpenTelemetry PHP organization](https://github.com/opentelemetry-php).

# Project Status

![Current Version](https://img.shields.io/github/v/tag/open-telemetry/opentelemetry-php)

| Signal  | Status | Project |
|---------|--------|---------|
| Traces  | Beta   | N/A     |
| Metrics | Beta   | N/A     |
| Logs    | Alpha  | N/A     |

## Specification conformance
We attempt to keep the [OpenTelemetry Specification Matrix](https://github.com/open-telemetry/opentelemetry-specification/blob/master/spec-compliance-matrix.md) up to date in order to show which features are available and which have not yet been implemented.

If you find an inconsistency in the data in the matrix vs. the data in this repository, please let us know in our slack channel and we'll get it rectified.

---

## Backwards Compatibility
We aim to provide backward compatibility (without any guarantee) even for alpha releases, however the library will raise notices indicating breaking changes and what to do about them. \
If you don't want these notices to appear or change the error message level, you can do so by calling:
```php
OpenTelemetry\SDK\Common\Dev\Compatibility\Util::setErrorLevel(0)
``` 
to turn messages off completely, or (for example)
```php
OpenTelemetry\SDK\Common\Dev\Compatibility\Util::setErrorLevel(E_USER_DEPRECATED)
``` 
to trigger only deprecation notices. Valid error levels are `0` (none), `E_USER_DEPRECATED`, `E_USER_NOTICE`, `E_USER_WARNING` and `E_USER_ERROR`  \
However (as long as in alpha) it is safer to pin a dependency on the library to a specific version and/or make the adjustments 
mentioned in the provided messages, since doing otherwise may break things completely for you in the future!

# Requirements

The library and all separate packages requires a PHP version of 7.4+

If you want to try out open-telemetry, you can install the entire [open-telemetry](https://packagist.org/packages/open-telemetry/opentelemetry) package, which includes the API, SDK, exporters and extensions.

For a production install, we recommend installing only the components that you need, for example API, SDK, and an exporter.

## Required dependencies
### 1) Install PSR17/18 implementations

The **SDK** and **Contrib** packages have a dependency on both a [HTTP Factories (PSR17)](https://www.php-fig.org/psr/psr-17/)
and a [php-http/async-client](https://docs.php-http.org/en/latest/clients.html) implementation.
You can find appropriate composer packages implementing given standards on [packagist.org](https://packagist.org/).
Follow [this link](https://packagist.org/providers/psr/http-factory-implementation) to find a `PSR17 (HTTP factories)` implementation,
and [this link](https://packagist.org/providers/php-http/async-client-implementation) to find a `php-http/async-client` implementation.

---

## Optional dependencies

### 1) Install PHP [ext-grpc](https://pecl.php.net/package/gRPC)

**The PHP gRPC extension is only needed, if you want to use the OTLP GRPC Exporter from the Contrib package.**

Three ways to install the gRPC extension are described below. Keep in mind, that whatever way
to install the extension you choose, the compilation can take up to 10-15 minutes. (As an alternative you can search for
a pre-compiled extension binary for your OS and PHP version, or you might be lucky and the package manager of your OS
provides a package for the extension)
- **Installation with pecl installer** (which should come with your PHP installation):

```bash
[sudo] pecl install grpc
```

- **Installation with pickle installer** (which you can find [here](https://github.com/FriendsOfPHP/pickle)):

```bash
[sudo] pickle install grpc
```
- **Manually compiling the extension**, which is not really complicated either, but you should know
  what you are doing, so we won't cover it here.

> Notice: The artifact of the gRPC extension can be as large as 100mb (!!!), Some 'hacks' to reduce that size,
>are mentioned [in this thread](https://github.com/grpc/grpc/issues/23626). **Use at your own risk.**

### 2) Install PHP [ext-mbstring](https://www.php.net/manual/en/book.mbstring.php)

The library's components will load the `symfony/polyfill-mbstring` package, but for better performance you should install
the  PHP mbstring extension. You can use the same install methods as described for the gRPC extension above,
however most OS` package managers provide a package for the extension.

### 3) Install PHP [ext-zlib](https://www.php.net/manual/en/book.zlib.php)

In order to use compression in HTTP requests you should install
the  PHP zlib extension. You can use the same install methods as described for the gRPC extension above,
however most OS` package managers provide a package for the extension.

### 4) Install PHP [ext-ffi](https://www.php.net/manual/en/book.ffi.php)

Support for using fibers in PHP 8.1 for Context storage requires the `ffi` extension, and can
be enabled by setting the `OTEL_PHP_FIBERS_ENABLED` environment variable to a truthy value (`1`, `true`, `on`).

Using fibers with non-`CLI` SAPIs may require preloading of bindings. One way to achieve this is setting [`ffi.preload`](https://www.php.net/manual/en/ffi.configuration.php#ini.ffi.preload) to `src/Context/fiber/zend_observer_fiber.h` and setting [`opcache.preload`](https://www.php.net/manual/en/opcache.preloading.php) to `vendor/autoload.php`.

### 5) Install PHP [ext-protobuf](https://pecl.php.net/package/protobuf)

**The PHP protobuf extension is recommended when using the `otlp` exporter from the Contrib package.**

The protobuf extension makes both exporters _significantly_ more performant, and we recommend that you do not use the PHP package in production. _Note that protobuf 3.20.0+ is required for php 8.1 support_

---

# Installation

The recommended way to install the library's packages is through [Composer](http://getcomposer.org):

Install Composer using the [installation instructions](https://getcomposer.org/doc/00-intromd#installation-linux-unix-macos) and add
```bash
 "minimum-stability": "dev"
```

To your project's `composer.json` file, as this library has not reached a stable release status yet.

To install the complete library with all packages you can run:

```bash
$ composer require open-telemetry/opentelemetry
```
This is perfect for trying out our examples or demos.


# Getting Started

You can find a getting started guide on [opentelemetry.io](https://opentelemetry.io/docs/php/getting-started/)

OpenTelemetry's goal is to provide a single set of APIs to capture _signals_, such as distributed traces and metrics, from your application and send them to an observability platform. This project allows you to do just that for applications written in PHP. There are two steps to this process: instrument your application, and configure an exporter.

To start capturing signals from your application it first  needs to be instrumented.

## Using OpenTelemetry in an Application

Your application should only depend on Interfaces provided by the API package:

```bash
$ composer require open-telemetry/api
```
In the best case you will use [Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) and write an adapter to not depend on the API directly.

Make sure your application works with a dependency on the API only, however to make full use of the library you want to install the **SDK** package and probably the **Contrib** package as well:

```bash
$ composer require open-telemetry/sdk
```
or
```bash
$ composer require open-telemetry/sdk open-telemetry/sdk-contrib
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

If all [configuration](https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration) is provided via environment variables or `php.ini`, then an SDK can be auto-loaded.
SDK autoloading must be enabled via the `OTEL_PHP_AUTOLOAD_ENABLED` setting, and will be performed as part of composer autoloading.

See [autoload_sdk.php example](./examples/autoload_sdk.php)

## Configuration

The SDK supports most of the configurations described in the specification: https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration

There are also a number of PHP-specific configurations:

| Name                                | Default value | Values                                                     | Example        | Description                                         |
|-------------------------------------|---------------|------------------------------------------------------------|----------------|-----------------------------------------------------|
| OTEL_PHP_TRACES_PROCESSOR           | batch         | batch, simple                                              | simple         | Span processor selection                            |
| OTEL_PHP_DETECTORS                  | all           | env, host, os, process, process_runtime, sdk, sdk_provided | env,os,process | Resource detector selection                         |
| OTEL_PHP_AUTOLOAD_ENABLED           | false         | true, false                                                | true           | Enable/disable SDK autoloading                      |
| OTEL_PHP_DISABLED_INSTRUMENTATIONS  | []            | Instrumentation name(s)                                    | psr15,psr18    | Disable one or more installed auto-instrumentations |

Configurations can be provided as environment variables, or via `php.ini` (or a file included by `php.ini`)

## Trace signals

### Auto-instrumentation

Auto-instrumentation is available via our [otel_instrumentation](https://github.com/open-telemetry/opentelemetry-php-instrumentation) PHP extension, and there are some auto-instrumentation modules available in our [contrib repo](https://github.com/open-telemetry/opentelemetry-php-contrib/tree/main/src/Instrumentation).

### Framework instrumentation

* [Symfony SDK Bundle](https://github.com/open-telemetry/opentelemetry-php-contrib/tree/main/src/Symfony/OtelSdkBundle) is the recommended way to use opentelemetry-php with symfony

### Manual instrumentation

If you wish to build your own instrumentation for your application, you will need to use the API, the SDK, and probably the contrib module (which contains most of the exporters).

#### Set up a tracer
Tracers must be obtained from a `TracerProvider`:

```php
$transport = (new OpenTelemetry\Contrib\Grpc\GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::TRACE));
$exporter = new OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
$tracerProvider = new \OpenTelemetry\SDK\Trace\TracerProvider(
    new \OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor(
        $exporter,
        ClockFactory::getDefault()
    )
);
\OpenTelemetry\SDK\Common\Util\ShutdownHandler::register([$tracerProvider, 'shutdown']);
$tracer = $tracerProvider->getTracer('example');
```

It's important to run the tracer provider's `shutdown()` method when the PHP process ends, to enable flushing of any enqueued telemetry.
The shutdown process is blocking, so consider running it in an async process. Otherwise, you can use the `ShutdownHandler` to register the shutdown function as part of PHP's shutdown process, as demonstrated above.

#### Creating spans

```php
$span = $tracer->spanBuilder('root')->startSpan();
//do some work
$span->end();
```

#### Nesting spans

You can _activate_ a span, so that it will be the parent of future spans.

When you activate a span, it's critical that you also _detach_ it when done. We recommend doing this in a `finally` block:
```php
$root = $tracer->spanBuilder('root')->startSpan();
$scope = $root->activate();
try {
    $child = $tracer->spanBuilder('child')->startSpan();
    $child->end();
} finally {
    $root->end();
    $scope->detach();
}
```
When an active span is deactivated (scope detached), the previously active span will become the active span again.

#### Distributed tracing
OpenTelemetry supports distributed tracing via [Context Propagation](https://opentelemetry.io/docs/concepts/signals/traces/#context-propagation), where traces can be correlated across multiple services. To enable this, outgoing HTTP requests must be injected with standardized headers which are understood by other OTEL-enabled services.

```php
$request = new Request('GET', 'https://www.example.com');
$carrier = [];
TraceContextPropagator::getInstance()->inject($carrier);
foreach ($carrier as $name => $value) {
    $request = $request->withAddedHeader($name, $value);
}
$response = $client->send($request);
```

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
_frozen pending delivery of tracing and metrics_

# Versioning

Versioning rationale can be found in the [Versioning Documentation](/docs/versioning.md)

# Contributing

We would love to have you on board, please see our [Development README](./DEVELOPMENT.md) and [Contributing README](./CONTRIBUTING.md).
