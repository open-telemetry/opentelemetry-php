# OpenTelemetry php library

[![Gitter](https://badges.gitter.im/open-telemetry/opentelemetry-php.svg)](https://gitter.im/open-telemetry/opentelemetry-php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
![CI Build](https://github.com/open-telemetry/opentelemetry-php/workflows/PHP%20Composer/badge.svg)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)

## Current Project Status
![Current Version](https://img.shields.io/github/v/tag/open-telemetry/opentelemetry-php)

This project currently lives in a pre-alpha status.  Our current release is not production ready; it has been created in order to receive feedback from the community.

We keep the [OpenTelemetry Specification Matrix](https://github.com/open-telemetry/opentelemetry-specification/blob/master/spec-compliance-matrix.md) up to date in order to show
 which features are available and which have not yet been implemented.
 
## Communication
Most of our communication is done on CNCF Slack, in the [otel-php](https://cloud-native.slack.com/archives/C01NFPCV44V) channel. To sign up, create a CNCF slack account here http://slack.cncf.io/

Our meetings are held weekly on zoom on Wednesdays at 10:30am PST / 1:30pm EST.  
A Google calendar invite with the included zoom link can be found [here](https://calendar.google.com/event?action=TEMPLATE&tmeid=N2VtZXZmYnVmbzZkYjZkbTYxdjZvYTdxN21fMjAyMDA5MTZUMTczMDAwWiBrYXJlbnlyeHVAbQ&tmsrc=google.com_b79e3e90j7bbsa2n2p5an5lf60%40group.calendar.google.com&scp=ALL)

Our open issues can all be found in the [github issues tab](https://github.com/open-telemetry/opentelemetry-php/issues).  Feel free to reach out in gitter if you have any additional questions about these issues; we are always happy to talk through implementation details.

## Installation
The recommended way to install the library is through [Composer](http://getcomposer.org):

1.)  You'll need to add a  
```bash
 "minimum-stability": "dev"
```

To your project's `composer.json` file, as this utility has not reached a stable release status quite yet.

2.)  Install the dependency with composer:

```bash
$ composer require open-telemetry/opentelemetry
```

## Development
We use docker and docker-compose to perform a lot of our static analysis and testing.  

If you're planning to develop for this library, it'll help to install `docker engine` and `docker-compose`.  
You can find installation instructions for these packages can be found [here](https://docs.docker.com/install/), under the `Docker Engine` and `Docker Compose` submenus respectively.

## Proto Generation
In order to generate proto files for use with this repository, we can perform a 

`make proto`

From the root directory.  This wil create a `/proto` folder in the root directory of the repository.

## Styling
We use [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) for our code linting and standards fixer.  The associated configuration for this standards fixer can be found in the root of the repository [here](https://github.com/open-telemetry/opentelemetry-php/blob/master/.php_cs)

To ensure that your code is stylish, you can execute `make style` from your bash compatible shell.  This process will print out the fixes that it is making to your associated files.  Usually this process is performed as part of a code checkin.  This process runs during CI and is a required check.  Code that doesn't follow this style pattern will emit a failure in CI.

## Static Analysis
We use [Phan](https://github.com/phan/phan/) for static analysis.  Currently our phan configuration is just a standard default analysis configuration.  You can use our phan docker wrapper to easily perform static analysis on your changes.

Execute `make phan` from your bash compatible shell.
This process will return 0 on success.
Usually this process is performed as part of a code checkin.  This process runs during CI and is a required check.  Code that doesn't match the standards that we have defined in our [phan config](https://github.com/open-telemetry/opentelemetry-php/blob/master/.phan/config.php) will emit a failure in CI. 

## Testing
To make sure the tests in this repo work as you expect, you can use the included docker test wrapper.  
Execute `make test` from your bash compatible shell.  This will output the test output as well as a test coverage analysis.  Code that doesn't pass our currently defined tests will emit a failure in CI

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
