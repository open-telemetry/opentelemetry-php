[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/sem-conv/releases)
[![Source](https://img.shields.io/badge/source-sem--conv-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/SemConv)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:sem--conv-blue)](https://github.com/opentelemetry-php/sem-conv)
[![Latest Version](http://poser.pugx.org/open-telemetry/sem-conv/v/unstable)](https://packagist.org/packages/open-telemetry/sem-conv/)
[![Stable](http://poser.pugx.org/open-telemetry/sem-conv/v/stable)](https://packagist.org/packages/open-telemetry/sem-conv/)

# OpenTelemetry Semantic Conventions

Common semantic conventions used by OpenTelemetry implementations across all languages.

Please note that Semantic Conventions are tagged with the version of the
[Semantic Conventions](https://github.com/open-telemetry/semantic-conventions/tags) that they were generated from.

See https://opentelemetry.io/docs/concepts/semantic-conventions/.

# Stability

Semantic conventions are separated into `stable` and `incubating`. Both are generated from the
[Semantic Conventions](https://github.com/open-telemetry/semantic-conventions) release with the same version as this package.

Instrumentation authors should use stable conventions where possible, but may use incubating conventions.

Deprecated conventions are kept and marked `@deprecated`, with their replacement where upstream names one. They are
not removed in a minor or patch release, following the
[semantic conventions guidance for code generation](https://github.com/open-telemetry/semantic-conventions/blob/v1.45.0/docs/non-normative/code-generation.md#deprecated-conventions).
Move to the replacement when you can.

## Stable

Stable semantic conventions (`OpenTelemetry\SemConv\Attributes\*` and `OpenTelemetry\SemConv\Metrics\*`) are based
on all elements marked as `stable` and are considered safe for use in production.

## Incubating

Incubating semantic conventions (`OpenTelemetry\SemConv\Incubating\*`) contain both `stable` and `experimental` (in-development)
elements. Experimental elements should be used with caution.

## Installation

```shell
composer require open-telemetry/sem-conv
```

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
