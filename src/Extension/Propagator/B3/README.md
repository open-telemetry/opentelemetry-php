[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/extension-propagator-b3/releases)
[![Source](https://img.shields.io/badge/source-extension--propagator--b3-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/Extension/Propagator/B3)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:extension--propagator--b3-blue)](https://github.com/opentelemetry-php/extension-propagator-b3)
[![Latest Version](http://poser.pugx.org/open-telemetry/extension-propagator-b3/v/unstable)](https://packagist.org/packages/open-telemetry/extension-propagator-b3/)
[![Stable](http://poser.pugx.org/open-telemetry/extension-propagator-b3/v/stable)](https://packagist.org/packages/open-telemetry/extension-propagator-b3/)

# OpenTelemetry Extension
### B3 Propagator

B3 is a propagator that supports the specification for the header "b3" used for trace context propagation across
service boundaries.(https://github.com/openzipkin/b3-propagation). OpenTelemetry PHP B3 Propagator Extension provides
option to use B3 single header(https://github.com/openzipkin/b3-propagation#single-header) as well as B3 multi header
(https://github.com/openzipkin/b3-propagation#multiple-headers) propagators.

### Usage
For B3 single header:
```text
B3Propagator::getB3SingleHeaderInstance()
```

For B3 multi header:
```text
B3Propagator::getB3MultiHeaderInstance()
```

Both of the above have `extract` and `inject` methods available to extract and inject respectively into the
header.

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
