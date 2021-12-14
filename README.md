# OpenTelemetry php library

![CI Build](https://github.com/open-telemetry/opentelemetry-php/workflows/PHP%20Composer/badge.svg)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)

## Current Project Status
![Current Version](https://img.shields.io/github/v/tag/open-telemetry/opentelemetry-php)

This project currently lives in a pre-alpha status.  Our current release is not production ready; it has been created in order to receive feedback from the community.

We attempt to keep the [OpenTelemetry Specification Matrix](https://github.com/open-telemetry/opentelemetry-specification/blob/master/spec-compliance-matrix.md) up to date in order to show which features are available and which have not yet been implemented.  

If you find an inconsistency in the data in the matrix vs. the data in this repository, please let us know in our slack channel and we'll get it rectified.

## Communication
Most of our communication is done on CNCF Slack, in the [otel-php](https://cloud-native.slack.com/archives/C01NFPCV44V) channel. To sign up, create a CNCF slack account here http://slack.cncf.io/

Our meetings are held weekly on zoom on Wednesdays at 10:30am PST / 1:30pm EST.  
A Google calendar invite with the included zoom link can be found [here](https://calendar.google.com/event?action=TEMPLATE&tmeid=N2VtZXZmYnVmbzZkYjZkbTYxdjZvYTdxN21fMjAyMDA5MTZUMTczMDAwWiBrYXJlbnlyeHVAbQ&tmsrc=google.com_b79e3e90j7bbsa2n2p5an5lf60%40group.calendar.google.com&scp=ALL)

Our open issues can all be found in the [github issues tab](https://github.com/open-telemetry/opentelemetry-php/issues).  Feel free to reach out on Slack if you have any additional questions about these issues; we are always happy to talk through implementation details.

## Requirements
The library requires a PHP version of 7.4.x or 8.0.x (PHP 8.1 compability is in the works)

### Dependencies

---

#### REQUIRED DEPENDENCIES
#### 1.) Install PSR17/18 implementations

The library has a dependency on both a [HTTP Factories (PSR17)](https://www.php-fig.org/psr/psr-17/)
and a [php-http/async-client](https://docs.php-http.org/en/latest/clients.html) implementation.
You can find appropriate composer packages implementing given standards on [packagist.org](https://packagist.org/).
Follow [this link](https://packagist.org/providers/psr/http-factory-implementation) to find a `PSR17 (HTTP factories)` implementation,
and [this link](https://packagist.org/providers/php-http/async-client-implementation) to find a `php-http/async-client` implementation.

---

#### OPTIONAL DEPENDENCIES

#### 1.) Install PHP [ext-grpc](https://pecl.php.net/package/gRPC)

**The PHP gRPC extension is only needed, if you want to use the OTLP GRPC Exporter.**

There are basically three ways to install the gRPC extension which will be described below. Keep in mind, that whatever way
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

> Notice: The artifact of the gRPC extension can be as large as 100mb (!!!), there are 'hacks' to reduce that size,
> which you can find [in this thread](https://github.com/grpc/grpc/issues/23626). Use at your own risk.

#### 2.) Install PHP [ext-mbstring](https://www.php.net/manual/en/book.mbstring.php)

The library will load the `symfony/polyfill-mbstring` package, but for better performance you should install
the  PHP mbstring extension. You can use the same install methods as described for the gRPC extension above,
however most OS` package managers provide a package for the extension.

#### 3.) Install PHP [ext-zlib](https://www.php.net/manual/en/book.zlib.php)

In order to use compression in HTTP requests you should install
the  PHP zlib extension. You can use the same install methods as described for the gRPC extension above,
however most OS` package managers provide a package for the extension.

---

## Installation
The recommended way to install the library is through [Composer](http://getcomposer.org):

1.)  Install the composer package using [Composer's installation instructions](https://getcomposer.org/doc/00-intromd#installation-linux-unix-macos).

2.)  Add
```bash
 "minimum-stability": "dev"
```

To your project's `composer.json` file, as this utility has not reached a stable release status yet.

2.)  Install the dependency with composer:

```bash
$ composer require open-telemetry/opentelemetry
```

## Development
We use `docker` and `docker-compose` to perform a lot of our static analysis and testing.  

If you're planning to develop for this library, it'll help to install `docker engine` and `docker-compose`.  

You can find installation instructions for these packages can be found [here](https://docs.docker.com/install/), under the `Docker Engine` and `Docker Compose` submenus respectively.

To ensure you have all the correct packages installed locally in your dev environment, you can run

```bash
make install
```

From your bash compatible shell.  This will install all of the necessary vendored libraries that the project uses to
the `/vendor` directory.

To update these dependencies, you can run

```bash
make update
```

In order to update all the vendored libraries in the `/vendor` directory.


## Pull Requests

Once you've made the update to the codebase that you'd like to submit, you may [create a pull request](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request) to the opentelemetry-php project.

After you open the pull request, the CI/CD pipeline will run all of the associated [github actions](https://github.com/open-telemetry/opentelemetry-php/actions/workflows/php.yml).

You can simulate the important github actions locally before you submit your PR by running the following command:

```bash
make all
```

from your bash compatible shell.  This does the following things:

* Installs/updates all the required dependencies for the project
* Uses [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to style your code using our style preferences.
* Runs all of our [phpunit](https://phpunit.de/) unit tests.
* Performs static analysis with [Phan](https://github.com/phan/phan), [Psalm](https://psalm.dev/) and [PHPStan](https://phpstan.org/user-guide/getting-started)

### Other PHP versions

We aim to support officially supported PHP versions, according to https://www.php.net/supported-versions.php. The developer image `ghcr.io/open-telemetry/opentelemetry-php/opentelemetry-php-base` is tagged as `7.4`, `8.0` and `8.1` respectively, with `7.4` being the default.
You can execute the test suite against other PHP versions by running the following command:

```bash
PHP_VERSION=8.0 make all
#or
PHP_VERSION=8.1 make all
```

## Proto Generation
Our proto files are committed to the repository into the `/proto` folder.  These are used in gRPC connections to the
upstream.  These get updated when the [opentelemetry-proto](https://github.com/open-telemetry/opentelemetry-proto)
repo has a meaningful update.  The maintainer SIG is discussing a way to make this more automatic in the future.

If you'd like to generate proto files for use with this repository, one can run the following command:

```bash
make proto
```

From your bash compatible shell in the root of this directory.  This wil create a `/proto` folder in the root
directory of the
repository.


## Semantic Conventions Generation
A generated semantic convention files are committed to the repository in the `/src/SemConv` directory. These files
get updated when new version of [opentelemetry-specification](https://github.com/open-telemetry/opentelemetry-specification)
released.

```bash
SEMCONV_VERSION=1.8.0 make semconv
```

Run it from you bash compatible shell in the root of this repository.

## Styling
We use [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) for our code linting and standards fixer.  The associated configuration for this standards fixer can be found in the root of the repository [here](https://github.com/open-telemetry/opentelemetry-php/blob/master/.php_cs)

To ensure that your code is stylish, you can run the follwing command:
```bash
make style
```

from your bash compatible shell.  This process will print out the fixes that it is making to your
associated files.  Usually this process is performed as part of a code checkin.  This process runs during CI and is a required check.  Code that doesn't follow this style pattern will emit a failure in CI.

## Static Analysis
We use [Phan](https://github.com/phan/phan/) for static analysis.  Currently our phan configuration is just a standard default analysis configuration.  You can use our phan docker wrapper to easily perform static analysis on your changes.

To run Phan, one can run the following command:
```bash
make phan
```
from your bash compatible shell.
This process will return 0 on success.
Usually this process is performed as part of a code checkin.  This process runs during CI and is a required check.  Code that doesn't match the standards that we have defined in our [phan config](https://github.com/open-telemetry/opentelemetry-php/blob/master/.phan/config.php) will emit a failure in CI.

We also use [Psalm](https://psalm.dev/) as a second static analysis tool.  
You can use our psalm docker wrapper to easily perform static analysis on your changes.

To run Psalm, one can run the following command:
```bash
make psalm
```
from your bash compatible shell. This process will return 0 on success. Usually this process is performed as part of a code checkin. This process runs during CI and is a required check. Code that doesn't match the standards that we have defined in our [psalm config](https://github.com/open-telemetry/opentelemetry-php/blob/main/psalm.xml.dist) will emit a failure in CI.

We use [PHPStan](https://github.com/phpstan/phpstan) as our third tool for static analysis.
You can use our PHPStan docker wrapper to easily perform static analysis on your changes.

Execute `make phpstan` from your bash compatible shell. This process will return 0 on success. Usually this process is
performed as part of a code checkin. This process runs during CI and is a required check. Code that doesn't match the
standards that we have defined in
our [PHPStan config](https://github.com/open-telemetry/opentelemetry-php/blob/main/phpstan.neon.dist) will emit a failure
in CI.

## Testing
To make sure the tests in this repo work as you expect, you can use the included docker test wrapper.  
To run the test suite, execute
```bash
make test
```
from your bash compatible shell.  This will output the test output as well
as a test coverage analysis.  Code that doesn't pass our currently defined tests will emit a failure in CI

## Examples

### Trace
You can use the [examples/AlwaysOnZipkinExample.php](/examples/AlwaysOnZipkinExample.php) file to test out the reference implementation we have for zipkin.  This example perfoms a sample trace with a grouping of 5 spans and POSTs the result to a local zipkin instance.

You can also use the [examples/AlwaysOnJaegerExample.php](/examples/AlwaysOnJaegerExample.php) file to test out the reference implementation we have for jaegar.  This example perfoms a sample trace with a grouping of 5 spans and POSTs the result to a local jaegar instance.

You can use the [examples/AlwaysOnZipkinToNewrelicExample.php](/examples/AlwaysOnZipkinToNewrelicExample.php) file to test out the reference implementation we have for zipkin to Newrelic.  This example perfoms a sample trace with spans and POSTs the result to a Newrelic endpoint.  This requires a license key (free accounts available) to be set in the environment (NEW_RELIC_INSERT_KEY) to authenticate to the backend.

You can use the [examples/AlwaysOnNewrelicExample.php](/examples/AlwaysOnNewrelicExample.php) file to test out the reference implementation we have for Newrelic.  This example perfoms a sample trace with spans and POSTs the result to a Newrelic endpoint.  This requires a license key (free accounts available) set in the environment (NEW_RELIC_INSERT_KEY) to authenticate to the backend.

The PHP for all examples should execute by itself (if you have a zipkin or jaegar instance running on localhost), but if you'd like a no-fuss way to test this out with docker and docker-compose, you can perform the following simple steps:

1.)  Install the necessary dependencies by running `make install`.  This will install the composer dependencies and store them in `/vendor`  
2.)  Execute the example trace using `make trace examples`.

Exported spans can be seen in zipkin at [http://127.0.0.1:9411](http://127.0.0.1:9411)

Exported spans can also be seen in jaeger at [http://127.0.0.1:16686](http://127.0.0.1:16686)

### Metrics
You can use the [examples/prometheus/PrometheusMetricsExample.php](/examples/prometheus/PrometheusMetricsExample.php) file to test out the reference implementation we have. This example will create a counter that will be scraped by local Prometheus instance.

The easy way to test the example out with docker and docker-compose is:

1.) Run `make metrics-prometheus-example`. Make sure that local ports 8080, 6379 and 9090 are available.

2.) Open local Prometheus instance: http://localhost:9090

3.) Go to Graph section, type "opentelemetry_prometheus_counter" in the search field or select it in the dropdown menu. You will see the counter value. Every other time you run `make metrics-prometheus-example` will increment the counter but remember that Prometheus scrapes values once in 10 seconds.

4.) In order to stop docker containers for this example just run `make stop-prometheus`

## User Integration Guides
* [Integrating OpenTelemetry PHP into Laravel Applications](./docs/laravel-integration.md)
* [Integrating OpenTelemetry PHP into Symfony Applications](./docs/symfony-integration.md)
## Versioning

Versioning rationale can be found in the [Versioning Documentation](/docs/versioning.md)
