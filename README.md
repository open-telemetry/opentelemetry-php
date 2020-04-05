# OpenTelemetry php library
[![Gitter](https://badges.gitter.im/open-telemetry/opentelemetry-php.svg)](https://gitter.im/open-telemetry/opentelemetry-php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![Build Status](https://travis-ci.org/open-telemetry/opentelemetry-php.svg?branch=master)](https://travis-ci.org/open-telemetry/opentelemetry-php)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)

## Communication
Most of our communication is done on gitter.im in the [opentelemetry-php](https://gitter.im/open-telemetry/opentelemetry-php) channel.

Our meetings are held weekly on zoom on Wednesdays at 10:30am PST / 1:30pm EST.  
A Google calendar invite with the included zoom link can be found [here](https://calendar.google.com/event?action=TEMPLATE&tmeid=NmkyMTM1cWtlNGlmODdrMTZsZW9qa2tzdDRfMjAyMDAyMTlUMTgzMDAwWiBnb29nbGUuY29tX2I3OWUzZTkwajdiYnNhMm4ycDVhbjVsZjYwQGc&tmsrc=google.com_b79e3e90j7bbsa2n2p5an5lf60%40group.calendar.google.com&scp=ALL)

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

## Examples
You can use the [examples/AlwaysSampleTraceExample.php](/open-telemetry/opentelemetry-php/blob/master/examples/AlwaysOnTraceExample.php) file to test out the reference implementation we have.

The PHP should execute by itself, but if you'd like a no-fuss way to test this out with docker and docker-compose, you can perform the following simple steps:

1.)  Start the local zipkin server by running `docker-compose up -d`   
2.)  Install the necessary dependencies by running `make install`.  This will install the composer dependencies and store them in `/vendor`  
2.)  Execute the example trace using `make example`.  

Exported spans can be seen in zipkin at [http://127.0.0.1:9411](http://127.0.0.1:9411)

## Static Analysis
We use [Phan](https://github.com/phan/phan/) for static analysis.  Currently our phan configuration is just a standard default analysis configuration.  You can use our phan docker wrapper to easily perform static analysis on your changes.

Execute `make phan` from your bash compatible shell.

## Testing
To make sure the tests in this repo work as you expect, you can use the included docker test wrapper.  

Execute `make test` from your bash compatible shell.

## Caveats
The Span Links concept is not yet implemented.
