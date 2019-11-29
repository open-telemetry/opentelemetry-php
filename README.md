# OpenTelemetry php library
[![Gitter](https://badges.gitter.im/open-telemetry/opentelemetry-php.svg)](https://gitter.im/open-telemetry/opentelemetry-php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![Build Status](https://travis-ci.org/open-telemetry/opentelemetry-php.svg?branch=master)](https://travis-ci.org/open-telemetry/opentelemetry-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/open-telemetry/opentelemetry-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/open-telemetry/opentelemetry-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/open-telemetry/opentelemetry-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/open-telemetry/opentelemetry-php/?branch=master)

- [Installation](#installation)
- [Tracing](#tracing)
- [Test](#testing)

Our meetings are held weekly on Wednesdays at 10:30am PST / 1:30pm EST.
Please reach out on [gitter.im](https://gitter.im/open-telemetry/community) if you'd like to be invited.
The public calendar invite will be shared once it becomes avaiable.

## Installation
The recommended way to install the library is through [Composer](http://getcomposer.org):
```bash
$ composer require open-telemetry/opentelemetry
```

### Examples

You can use the [examples/AlwaysSampleTraceExample.php](https://github.com/open-telemetry/opentelemetry-php/tree/master/examples/AlwaysSampleTraceExample.php) file to test out the reference implementation we have.  This can be easily executed with docker by running `./resources/example-using-docker` from the root of the repository.

## Testing
To make sure the tests in this repo work as you expect, you can use the included docker test wrapper:

1.)  Make sure that you have docker installed
2.)  Execute `./resources/test-using-docker` from your bash compatible shell.

## Caveats
The Span Links concept is not yet implemented.
