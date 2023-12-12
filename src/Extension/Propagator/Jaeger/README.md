[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/extension-propagator-jaeger/releases)
[![Source](https://img.shields.io/badge/source-extension--propagator--jaeger-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/Extension/Propagator/Jaeger)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:extension--propagator--jaeger-blue)](https://github.com/opentelemetry-php/extension-propagator-jaeger)
[![Latest Version](http://poser.pugx.org/open-telemetry/extension-propagator-jaeger/v/unstable)](https://packagist.org/packages/open-telemetry/extension-propagator-jaeger/)
[![Stable](http://poser.pugx.org/open-telemetry/extension-propagator-jaeger/v/stable)](https://packagist.org/packages/open-telemetry/extension-propagator-jaeger/)

# OpenTelemetry Extension
### Jaeger Propagator

Jaeger is a propagator that supports the specification for the header "uber-trace-id" used for trace context propagation across
service boundaries.(https://www.jaegertracing.io/docs/1.52/client-libraries/#propagation-format).

### Usage
For Jaeger single header:
```text
JaegerPropagator::getInstance()
```

Both of the above have `extract` and `inject` methods available to extract and inject respectively into the
header.

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
