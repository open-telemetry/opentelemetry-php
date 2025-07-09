# Contributing Guide

## Introduction

Welcome to the OpenTelemetry PHP repository! We appreciate your interest in contributing and helping improve this project. No contribution is too small—whether you're fixing a typo, improving documentation, or implementing a major feature, we value your help.

This repository is part of the larger OpenTelemetry ecosystem, aimed at providing observability solutions for PHP applications. If you have any questions, feel free to ask in our community channels (See further help below). Your contributions make a difference!

## Pre-requisites

To contribute effectively, ensure you have the following tools installed:

* PHP 8.1 or higher (Check supported PHP versions)

We aim to support officially supported PHP versions, according to https://www.php.net/supported-versions.php. The
developer image `ghcr.io/open-telemetry/opentelemetry-php/opentelemetry-php-base` is tagged as `8.1`, `8.2` and `8.3`
respectively, with `8.1` being the default. You can execute the test suite against other PHP versions by running the
following command:

```bash
PHP_VERSION=8.1 make all
#or
PHP_VERSION=8.3 make all
```
For repeatability and consistency across different operating systems, we use the [3 Musketeers pattern](https://3musketeers.pages.dev/). If you're on Windows, it might be a good idea to use Git bash for following the steps below.

**Note: After cloning the repository, copy `.env.dist` to `.env`.** 

Skipping the step above would result in a "`The "PHP_USER" variable is not set. Defaulting to a blank string`" warning

We use `docker` and `docker compose` to perform a lot of our static analysis and testing. If you're planning to develop for this library, it'll help to install
[docker engine](https://docs.docker.com/engine/install/) and the [compose plugin](https://docs.docker.com/compose/install/).

Development tasks are generally run through a `Makefile`. Running `make` or `make help` will list available targets.

## Workflow

### Pull Requests

To propose changes to the codebase, you need
to [open a pull request](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request)
to the opentelemetry-php project.

After you open the pull request, the CI will run all the
associated [github actions](https://github.com/open-telemetry/opentelemetry-php/actions/workflows/php.yml).

To ensure your PR doesn't emit a failure with GitHub actions, it's recommended that you run the important CI tests locally with the following command:

```bash
make all # composer update, then run all checks
make all-lowest # composer update to lowest dependencies, then run all checks
```

This does the following things:

* Installs/updates all the required dependencies for the project
* Uses [Rector](https://github.com/rectorphp/rector) to refactor your code according to our standards.
* Uses [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to style your code using our style preferences.
* Uses [Deptrac](https://github.com/qossmic/deptrac) to check for dependency violations inside our code base
* Makes sure the composer files for the different components are valid
* Runs all of our [phpunit](https://phpunit.de/) unit tests.
* Performs static analysis with [Phan](https://github.com/phan/phan), [Psalm](https://psalm.dev/)
  and [PHPStan](https://phpstan.org/user-guide/getting-started)

## Local Run/Build 

To ensure you have all the correct packages installed locally in your dev environment, you can run

```bash
make install
```

This will install all the library dependencies to
the `/vendor` directory.

To update these dependencies, you can run

```bash
make update
```

To downgrade to the lowest dependencies, you can run

```shell
make update-lowest
```

To run all checks without doing a composer update:

```shell
make all-checks
```
## Testing

To make sure the tests in this repo work as you expect, you can use the included docker test wrapper.  
To run the test suite, execute

```bash
make test
```

This will output the test output as well as a test coverage analysis (text + html - see `tests/coverage/html`). Code
that doesn't pass our currently defined tests will emit a failure in CI

## Contributing Rules

Even though it may not be reflected everywhere in the codebase yet, we aim to provide software which is easy to read and change.
The methods described in Clean Code book(s) by Robert C. Martin (Uncle Bob) are a de facto industry standards nowadays.
Reading those books is highly recommended, however you can take a look at the examples given at [Clean Code PHP](https://github.com/jupeter/clean-code-php). 
While we have no rule to strictly follow said methods and patterns, they are highly recommended as an orientation for 
your pull requests and to be referenced in reviews.

We might add additional guidelines regarding for example testing in the future.

## Additional Information

### Automatic Refactoring and Upgrading

We use [Rector](https://github.com/rectorphp/rector) to automatically refactor our code according to given standards
and upgrade the code to supported PHP versions.
The associated configuration can be found [here](./.rector.php)

If you want to check what changes would be applied by rector, you can run:

```bash
make rector
```
This command will simply print out the changes `rector` would make without actually changing any code.

To refactor your code following our given standards, you can run:

```bash
make rector-write
```

This command applies the changes to the code base.
Make sure to run `make style` (see below) after running the `rector`command as the changes might not follow our coding standard.

### Styling

We use [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) for our code linting and standards fixer. The
associated configuration can be found [here](./.php-cs-fixer.php)

To ensure that your code follows our coding standards, you can run:

```bash
make style
```

This command executes a required check that also runs during CI. This process performs the required fixes and prints them
out. Code that doesn't meet the style pattern will emit a failure with GitHub actions.

### Static Analysis

We use [Phan](https://github.com/phan/phan/) for static analysis. Currently, our phan configuration is just a standard
default analysis configuration. You can use our phan docker wrapper to easily perform static analysis on your changes.

To run Phan, one can run the following command:

```bash
make phan
```

This process will return 0 on success. Usually this process is performed as part of a code checkin. This process runs
during CI and is a required check. Code that doesn't match the standards that we have defined in
our [phan config](https://github.com/open-telemetry/opentelemetry-php/blob/master/.phan/config.php) will emit a failure
in CI.

We also use [Psalm](https://psalm.dev/) as a second static analysis tool.  
You can use our psalm docker wrapper to easily perform static analysis on your changes.

To run Psalm, one can run the following command:

```bash
make psalm
```

This process will return 0 on success. Usually this process is performed as part of a code checkin. This process runs
during CI and is a required check. Code that doesn't match the standards that we have defined in
our [psalm config](https://github.com/open-telemetry/opentelemetry-php/blob/main/psalm.xml.dist) will emit a failure in
CI.

We use [PHPStan](https://github.com/phpstan/phpstan) as our third tool for static analysis. You can use our PHPStan
docker wrapper to easily perform static analysis on your changes.

To perform static analysis with PHPStan run:

```bash
make phpstan
```

This process will return 0 on success. Usually this process is performed as part of a code checkin. This process runs
during CI and is a required check. Code that doesn't match the standards that we have defined in
our [PHPStan config](https://github.com/open-telemetry/opentelemetry-php/blob/main/phpstan.neon.dist) will emit a
failure in CI.

### Code Coverage
We use [codecov.io](https://about.codecov.io/) to track code coverage for this repo.  This is configured in the [php.yaml github action](https://github.com/open-telemetry/opentelemetry-php/blob/main/.github/workflows/php.yml#L71-L72).  We don't require a specific level of code coverage for PRs to pass - we just use this tool in order to understand how a PR will potentially change the amount of code coverage we have across the code base.  This tool isn't perfect - sometimes we'll see small deltas in code coverage where there shouldn't be any - this is nothing to fret about.

If code coverage does decrease on a pull request, you will see a red X in the CI for the repo, but that's ok - the reviewer will use their judgement to determine whether or not we have sufficient code coverage for the change.

### Dependency Validation

To make sure the different components of the library are distributable as separate packages, we have to check
    for dependency violations inside the code base. Dependencies must create a [DAC](https://en.wikipedia.org/wiki/Directed_acyclic_graph) in order to not create recursive dependencies.
For this purpose we use [Deptrac](https://github.com/qossmic/deptrac) and the respective configuration can be found
[here](./deptrac.yaml)

To validate the dependencies inside the code base, you can run:

```bash
make deptrac
```
This command will create an error for any violation of the defined dependencies. If you add new dependencies to the code base,
please configure them in the rector configuration.

## PhpMetrics

To generate a report showing a variety of metrics for the library and its classes, you can run:

```bash
make phpmetrics
```

This will generate a HTML PhpMetrics report in the `var/metrics` directory. Make sure to run `make test` before to
create the test log-file, used by the metrics report.

### Proto Generation

Our protobuf files are committed to the repository into the `/proto` folder. These are used in gRPC connections to the
upstream. These get updated when the [opentelemetry-proto](https://github.com/open-telemetry/opentelemetry-proto)
repo has a meaningful update. The maintainer SIG is discussing a way to make this more automatic in the future.

To generate protobuf files for use with this repository, you can run the following command:

```bash
make protobuf
```

This will replace `proto/otel/Opentelemetry` and `proto/otel/GPBMetadata` with freshly generated code based on the
latest tag from `opentelemetry-proto`, which can then be committed.

### Semantic Conventions Generation

Autogenerated semantic convention files are committed to the repository in the `/src/SemConv` directory. These files are
updated manually when a new version of [semantic-conventions](https://github.com/open-telemetry/semantic-conventions) is
released.

```bash
SEMCONV_VERSION=1.8.0 make semconv
```

Run this command in the root of this repository.

### API Documentation

We use [phpDocumentor](https://phpdoc.org/) to automatically generate API documentation from DocBlocks in the code.

To generate a recent version of the API documentation, you can run:

```bash
make phpdoc
```

To preview the documentation and changes you might expect, you can run:

```bash
make phpdoc-preview
```

This will start a HTTP server running at <http://localhost:8080> serving the updated documentation files.

## Maintainers

- [Bob Strecansky](https://github.com/bobstrecansky), Intuit
- [Brett McBride](https://github.com/brettmc/), Deakin University

For more information about the maintainer role, see the [community repository](https://github.com/open-telemetry/community/blob/main/community-membership.md#maintainer).

## Approvers

- [Ago Allikmaa](https://github.com/agoallikmaa)
- [Cedriz Ziel](https://github.com/cedricziel)
- [Chris Lightfoot-Wild](https://github.com/ChrisLightfootWild)

For more information about the approver role, see the [community repository](https://github.com/open-telemetry/community/blob/main/community-membership.md#approver).

## Triagers

For more information about the triager role, see the [community repository](https://github.com/open-telemetry/community/blob/main/community-membership.md#triagers).

## Emeritus maintainers/approvers/triagers

- [Timo Michna](https://github.com/tidal/)
- [Beniamin Calota](https://github.com/beniamin)
- [Fahmy Mohammed](https://github.com/Fahmy-Mohammed)
- [Levi Morrison](https://github.com/morrisonlevi)
- [Amber Zsistla](https://github.com/zsistla)
- [Jodee Varney](https://github.com/jodeev)
- [Przemek Delewski](https://github.com/pdelewski), Sumo Logic

For more information about the emeritus role, see the [community repository](https://github.com/open-telemetry/community/blob/main/guides/contributor/membership.md#emeritus-maintainerapprovertriager).

## Further Help

Most of our communication is done on CNCF Slack in the channel [otel-php](https://cloud-native.slack.com/archives/C01NFPCV44V).
To sign up, create a CNCF Slack account [here](http://slack.cncf.io/)

Our meetings are held weekly on Zoom on Wednesdays at [12:00 UTC](https://www.google.com/search?q=1200+utc+to+local).
A Google calendar invite with the included Zoom link can be found [here](https://calendar.google.com/event?action=TEMPLATE&tmeid=N2VtZXZmYnVmbzZkYjZkbTYxdjZvYTdxN21fMjAyMDA5MTZUMTczMDAwWiBrYXJlbnlyeHVAbQ&tmsrc=google.com_b79e3e90j7bbsa2n2p5an5lf60%40group.calendar.google.com&scp=ALL), and a Google Doc with the meeting agenda/minutes can be found [here](https://docs.google.com/document/d/1WLDZGLY24rk5fRudjdQAcx_u81ZQWCF3zxiNT-sz7DI).

Our open issues can all be found in the [GitHub issues tab](https://github.com/open-telemetry/opentelemetry-php/issues).  Feel free to reach out on Slack if you have any additional questions about these issues; we are always happy to talk through implementation details.


#### Thanks to all the people who already contributed!

<a href="https://github.com/open-telemetry/opentelemetry-php/graphs/contributors">
  <img src="https://contributors-img.web.app/image?repo=open-telemetry/opentelemetry-php" />
</a>
