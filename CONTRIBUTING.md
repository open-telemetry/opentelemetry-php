# Contributing to OpenTelemetry PHP

## Introduction

Welcome to the OpenTelemetry PHP repository! 🎉

Thank you for considering contributing to this project. Whether you're fixing a bug, adding new features, improving documentation, or reporting an issue, we appreciate your help in making OpenTelemetry better. 

This repository is part of the OpenTelemetry ecosystem, which provides observability tooling for distributed systems. Your contributions help enhance the PHP ecosystem for OpenTelemetry users worldwide.

If you have any questions, feel free to ask in the community channels. We’re happy to help! 😊

## Pre-requisites

Before getting started, ensure you have the following installed:

- **PHP** (8.1 or higher) – [Install PHP](https://www.php.net/downloads)
- **Composer** – [Install Composer](https://getcomposer.org/)
- **Docker & Docker Compose** – [Install Docker](https://docs.docker.com/engine/install/)
- **Make** (for running development tasks)

Additional Notes:
- Windows users may need [Git Bash](https://gitforwindows.org/) for better compatibility.
- Consider using [phpenv](https://github.com/phpenv/phpenv) for managing multiple PHP versions.

## Local Run/Build

To set up your local development environment:

```bash
# Clone the repository
git clone https://github.com/open-telemetry/opentelemetry-php.git
cd opentelemetry-php

# Install dependencies
make install
```

To update dependencies:

```bash
make update
```

To run checks:

```bash
make all-checks
```


## Testing

To ensure your changes meet our project's standards, simply run:

```bash
make all

```

To run tests against different PHP versions:

```bash
PHP_VERSION=8.3 make test
```

## Contributing Rules

- Follow [Clean Code PHP](https://github.com/jupeter/clean-code-php) principles.
- Ensure new features have appropriate test coverage.
- Run `make style` before submitting a PR.
- Include clear and concise documentation updates if needed.

Check for issues labeled [`good first issue`](https://github.com/open-telemetry/opentelemetry-php/issues?q=is%3Aissue+is%3Aopen+label%3A%22good+first+issue%22) to start contributing.


## Further Help

Need help? Join our community:

Most of our communication is done on CNCF Slack in the channel:
 
- **Slack**: [otel-php](https://cloud-native.slack.com/archives/C01NFPCV44V)
- **GitHub Discussions**: [OpenTelemetry PHP Discussions](https://github.com/open-telemetry/opentelemetry-php/discussions)
- **Issues**: If you encounter a bug, [open an issue](https://github.com/open-telemetry/opentelemetry-php/issues)


## Troubleshooting Guide

### Common Issues & Fixes

#### 1. Missing PHP dependencies
**Error:** `Class 'SomeClass' not found`

**Fix:** Run:
```bash
make install
```

#### 2. Linting Errors
**Error:** `Files not formatted correctly`

**Fix:** Run:
```bash
make style
```

#### 3. Tests Failing Due to Missing Dependencies
**Error:** `Dependency missing`

**Fix:**
```bash
make update
make test
```


## Additional Information

### Code Coverage
We use [Codecov](https://about.codecov.io/) to track test coverage. You can generate a local coverage report using:

```bash
make test-coverage
```

### Generating API Documentation
To generate API docs:
```bash
make phpdoc
```

To preview locally:
```bash
make phpdoc-preview
```

### Dependency Validation
We use [Deptrac](https://github.com/qossmic/deptrac) for dependency validation:
```bash
make deptrac
```

Thank you for contributing! 🚀



## Maintainers
[@open-telemetry/php-maintainers](https://github.com/orgs/open-telemetry/teams/php-maintainers)

- [Bob Strecansky](https://github.com/bobstrecansky)
- [Brett McBride](https://github.com/brettmc/), Deakin University

Find more about the maintainer role in [community repository](https://github.com/open-telemetry/community/blob/master/community-membership.md#maintainer)

## Approvers
[@open-telemetry/php-approvers](https://github.com/orgs/open-telemetry/teams/php-approvers)

Find more information about the approver role in the [community repository](https://github.com/open-telemetry/community/blob/master/community-membership.md#approver)

## Triagers
[@open-telemetry/php-triagers](https://github.com/orgs/open-telemetry/teams/php-triagers)

Find more information about the triager role in the [community repository](https://github.com/open-telemetry/community/blob/master/community-membership.md#triager)

## Members

- [Kishan Sangani](https://github.com/kishannsangani)

Find more information about the member role in the [community repository](https://github.com/open-telemetry/community/blob/master/community-membership.md#member)

## Emeritus maintainers/approvers/triagers

- [Timo Michna](https://github.com/tidal/)
- [Beniamin Calota](https://github.com/beniamin)
- [Fahmy Mohammed](https://github.com/Fahmy-Mohammed)
- [Levi Morrison](https://github.com/morrisonlevi)
- [Amber Zsistla](https://github.com/zsistla)
- [Jodee Varney](https://github.com/jodeev)
- [Przemek Delewski](https://github.com/pdelewski), Sumo Logic

Find more about emeritus roles in the [community repository](https://github.com/open-telemetry/community/blob/main/community-membership.md#emeritus-maintainerapprovertriager)


Our meetings are held weekly on zoom on Wednesdays at 10:30am PST / 1:30pm EST.
A Google calendar invite with the included zoom link can be found [here](https://calendar.google.com/event?action=TEMPLATE&tmeid=N2VtZXZmYnVmbzZkYjZkbTYxdjZvYTdxN21fMjAyMDA5MTZUMTczMDAwWiBrYXJlbnlyeHVAbQ&tmsrc=google.com_b79e3e90j7bbsa2n2p5an5lf60%40group.calendar.google.com&scp=ALL)

Our open issues can all be found in the [GitHub issues tab](https://github.com/open-telemetry/opentelemetry-php/issues).  Feel free to reach out on Slack if you have any additional questions about these issues; we are always happy to talk through implementation details.


#### Thanks to all the people who already contributed!

<a href="https://github.com/open-telemetry/opentelemetry-php/graphs/contributors">
  <img src="https://contributors-img.web.app/image?repo=open-telemetry/opentelemetry-php" />
</a>
