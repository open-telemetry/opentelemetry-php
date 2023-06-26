# OpenTelemetry for PHP

![CI Build](https://github.com/open-telemetry/opentelemetry-php/workflows/PHP%20QA/badge.svg)
[![codecov](https://codecov.io/gh/open-telemetry/opentelemetry-php/branch/master/graph/badge.svg)](https://codecov.io/gh/open-telemetry/opentelemetry-php)
[![Slack](https://img.shields.io/badge/slack-@cncf/otel--php-brightgreen.svg?logo=slack)](https://cloud-native.slack.com/archives/D03FAB6GN0K)

This is the **[monorepo](https://en.wikipedia.org/wiki/Monorepo)** for the **main** components of [OpenTelemetry](https://opentelemetry.io/) for PHP.

## Documentation

Please read the official documentation: https://opentelemetry.io/docs/instrumentation/php/

## Packages and versions

| Package              | Latest                                                                                                                                                                                                                                                                                                                                                  |
|----------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| API                  | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/api/v/stable)](https://packagist.org/packages/open-telemetry/api/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/api/v/unstable)](https://packagist.org/packages/open-telemetry/api/)                                                                                 |
| SDK                  | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/sdk/v/stable)](https://packagist.org/packages/open-telemetry/sdk/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/sdk/v/unstable)](https://packagist.org/packages/open-telemetry/sdk/)                                                                                 |
| Context              | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/context/v/stable)](https://packagist.org/packages/open-telemetry/context/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/context/v/unstable)](https://packagist.org/packages/open-telemetry/context/)                                                                 |
| Semantic Conventions | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/sem-conv/v/stable)](https://packagist.org/packages/open-telemetry/sem-conv/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/sem-conv/v/unstable)](https://packagist.org/packages/open-telemetry/sem-conv/)                                                             |
| OTLP Exporter        | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/exporter-otlp/v/stable)](https://packagist.org/packages/open-telemetry/exporter-otlp/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/exporter-otlp/v/unstable)](https://packagist.org/packages/open-telemetry/exporter-otlp/)                                         |
| gRPC Transport       | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/transport-grpc/v/stable)](https://packagist.org/packages/open-telemetry/transport-grpc/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/transport-grpc/v/unstable)](https://packagist.org/packages/open-telemetry/transport-grpc/)                                     |
| OTLP Protobuf Files  | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/gen-otlp-protobuf/v/stable)](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/gen-otlp-protobuf/v/unstable)](https://packagist.org/packages/open-telemetry/gen-otlp-protobuf/)                         |
| B3 Propagator        | [![Latest Stable Version](http://poser.pugx.org/open-telemetry/extension-propagator-b3/v/stable)](https://packagist.org/packages/open-telemetry/extension-propagator-b3/) [![Latest Unstable Version](http://poser.pugx.org/open-telemetry/extension-propagator-b3/v/unstable)](https://packagist.org/packages/open-telemetry/extension-propagator-b3/) |

Releases for both this repository and [contrib](https://github.com/open-telemetry/opentelemetry-php-contrib) are
based on read-only [git subtree splits](https://github.com/splitsh/lite) from our monorepo. You should refer to
[packagist.org](https://packagist.org/packages/open-telemetry/) for all packages, their versions and details.

You can also look at the read-only repositories, which live in the
[opentelemetry-php](https://github.com/opentelemetry-php) organization.

## Contributing

We would love to have you on board, please see our [Development README](./DEVELOPMENT.md) and [Contributing README](./CONTRIBUTING.md).

## Specification conformance

We attempt to keep the [OpenTelemetry Specification Matrix](https://github.com/open-telemetry/opentelemetry-specification/blob/master/spec-compliance-matrix.md) up to date in order to show which features are available and which have not yet been implemented.

If you find an inconsistency in the data in the matrix, please let us know in our slack channel and we'll get it rectified.

## Backwards compatibility

See [compatibility readme](src/SDK/Common/Dev/Compatibility/README.md).

## Versioning

Versioning rationale can be found in the [Versioning Documentation](/docs/versioning.md)
