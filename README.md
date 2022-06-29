# OpenTelemetry php library

![CI Build](https://github.com/open-telemetry/opentelemetry-php/workflows/PHP%20QA/badge.svg)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)

This is the **[monorepo](https://en.wikipedia.org/wiki/Monorepo)** for the **main** components of **[OpenTelemetry](https://opentelemetry.io/) PHP**. 
The main library is distributed as a complete package: [open-telemetry/opentelemetry](https://packagist.org/packages/open-telemetry/opentelemetry) 
as well as each component as a separate package. These packages are:

- API: [open-telemetry/api](https://packagist.org/packages/open-telemetry/api)
- SDK: [open-telemetry/sdk](https://packagist.org/packages/open-telemetry/sdk)
- Semantic Conventions: [open-telemetry/sem-conv](https://packagist.org/packages/open-telemetry/sem-conv)
- Context: [open-telemetry/context](https://packagist.org/packages/open-telemetry/context)
- Contrib: [open-telemetry/sdk-contrib](https://packagist.org/packages/open-telemetry/sdk-contrib)

---
This repository also hosts and distributes generated client code used by individual components as separate packages.  These packages are:
- Generated [OTLP](https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/otlp.md) ProtoBuf files:
  [open-telemetry/gen-otlp-protobuf](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf)
- Generated [Jaeger](https://github.com/jaegertracing/jaeger-idl) Thrift files:
  [open-telemetry/gen-jaeger-thrift](https://packagist.org/packages/open-telemetry/gen-jaeger-thrift)

For now the generated code packages are meant to be only used by library components internally.

---

The [OpenTelemetry PHP Contrib repository](https://github.com/open-telemetry/opentelemetry-php-contrib/) hosts contributions that are not part of the core
distribution or components of the library. Typically, these contributions are vendor specific receivers/exporters and/or
components that are only useful to a relatively small number of users.  

Additional packages, demos and tools are hosted or distributed in the [OpenTelemetry PHP organization](https://github.com/opentelemetry-php).

---
## Current Project Status

![Current Version](https://img.shields.io/github/v/tag/open-telemetry/opentelemetry-php)

This project currently lives in a **alpha status**.  Our current release is not production ready; it has been created in order to receive feedback from the community. \
As long as this project is in alpha status, things may and probably will break once in a while.  \
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

---

We attempt to keep the [OpenTelemetry Specification Matrix](https://github.com/open-telemetry/opentelemetry-specification/blob/master/spec-compliance-matrix.md) up to date in order to show which features are available and which have not yet been implemented.  

If you find an inconsistency in the data in the matrix vs. the data in this repository, please let us know in our slack channel and we'll get it rectified.

---

## Communication

Most of our communication is done on CNCF Slack in the channel [otel-php](https://cloud-native.slack.com/archives/C01NFPCV44V).
To sign up, create a CNCF Slack account [here](http://slack.cncf.io/)

Our meetings are held weekly on zoom on Wednesdays at 10:30am PST / 1:30pm EST.  
A Google calendar invite with the included zoom link can be found [here](https://calendar.google.com/event?action=TEMPLATE&tmeid=N2VtZXZmYnVmbzZkYjZkbTYxdjZvYTdxN21fMjAyMDA5MTZUMTczMDAwWiBrYXJlbnlyeHVAbQ&tmsrc=google.com_b79e3e90j7bbsa2n2p5an5lf60%40group.calendar.google.com&scp=ALL)

Our open issues can all be found in the [GitHub issues tab](https://github.com/open-telemetry/opentelemetry-php/issues).  Feel free to reach out on Slack if you have any additional questions about these issues; we are always happy to talk through implementation details.

## Requirements

The library and all separate packages requires a PHP version of 7.4.x, 8.0.x or 8.1.x

### Dependencies

---

#### REQUIRED DEPENDENCIES
#### 1) Install PSR17/18 implementations

The **SDK** and **Contrib** packages have a dependency on both a [HTTP Factories (PSR17)](https://www.php-fig.org/psr/psr-17/)
and a [php-http/async-client](https://docs.php-http.org/en/latest/clients.html) implementation.
You can find appropriate composer packages implementing given standards on [packagist.org](https://packagist.org/).
Follow [this link](https://packagist.org/providers/psr/http-factory-implementation) to find a `PSR17 (HTTP factories)` implementation,
and [this link](https://packagist.org/providers/php-http/async-client-implementation) to find a `php-http/async-client` implementation.

---

#### OPTIONAL DEPENDENCIES

#### 1) Install PHP [ext-grpc](https://pecl.php.net/package/gRPC)

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

#### 2) Install PHP [ext-mbstring](https://www.php.net/manual/en/book.mbstring.php)

The library's components will load the `symfony/polyfill-mbstring` package, but for better performance you should install
the  PHP mbstring extension. You can use the same install methods as described for the gRPC extension above,
however most OS` package managers provide a package for the extension.

#### 3) Install PHP [ext-zlib](https://www.php.net/manual/en/book.zlib.php)

In order to use compression in HTTP requests you should install
the  PHP zlib extension. You can use the same install methods as described for the gRPC extension above,
however most OS` package managers provide a package for the extension.

#### 4) Install PHP [ext-ffi](https://www.php.net/manual/en/book.ffi.php)

_Experimental_ support for using fibers in PHP 8.1 for Context storage requires the `ffi` extension, and can
be enabled by setting the `OTEL_PHP_FIBERS_ENABLED` environment variable to a truthy value (`1`, `true`, `on`).

Using fibers with non-`CLI` SAPIs may require preloading of bindings. One way to achieve this is setting [`ffi.preload`](https://www.php.net/manual/en/ffi.configuration.php#ini.ffi.preload) to `src/Context/fiber/zend_observer_fiber.h` and setting [`opcache.preload`](https://www.php.net/manual/en/opcache.preloading.php) to `vendor/autoload.php`.

#### 5) Install PHP [ext-protobuf](https://pecl.php.net/package/protobuf)

**The PHP protobuf extension is optional when using either the `OTLPHttp` or `OTLPGrpc` exporters from the Contrib package.**

The protobuf extension makes both exporters more performant. _Note that protobuf 3.20.0+ is required for php 8.1 support_

---

## Installation

The recommended way to install the library's packages is through [Composer](http://getcomposer.org):

Install Composer using the [installation instructions](https://getcomposer.org/doc/00-intromd#installation-linux-unix-macos) and add
```bash
 "minimum-stability": "dev"
```

To your project's `composer.json` file, as this library has not reached a stable release status yet.

### Getting Started with OpenTelemetry

To install the complete library with all packages you can run:

```bash
$ composer require open-telemetry/opentelemetry
```
This is perfect for trying out our examples or demos.

### Using OpenTelemetry in an Application

Your application should only depend on Interfaces provided by the API package:

```bash
$ composer require open-telemetry/api
```
In the best case you will use [Dependency Inversion](https://en.wikipedia.org/wiki/Dependency_inversion_principle) and write an adapter to not depend on the API directly.

Make sure your application works with a dependency on the API only, however to make really use of the library you want to install the **SDK** package and probably the **Contrib** package as well:

```bash
$ composer require open-telemetry/sdk
```
or
```bash
$ composer require open-telemetry/sdk open-telemetry/sdk-contrib
```
Make sure any **SDK** or **Contrib** code is set up by your configuration, bootstrap, dependency injection, etc.

### Using OpenTelemetry to instrument a Library

Your library should only depend on Interfaces provided by the API package:

```bash
$ composer require open-telemetry/api
```

For development and testing purposes you also want to install **SDK** and **Contrib** packages:
```bash
$ composer require --dev open-telemetry/sdk open-telemetry/sdk-contrib
```

## User Quickstarts

* [Exploring OpenTelemetry in Laravel Applications](./docs/laravel-quickstart.md)

## Framework integrations

* [Symfony SDK Bundle](https://github.com/open-telemetry/opentelemetry-php-contrib/tree/main/src/Symfony/OtelSdkBundle) is the recommended way to use opentelemetry-php with symfony

## Versioning

Versioning rationale can be found in the [Versioning Documentation](/docs/versioning.md)
