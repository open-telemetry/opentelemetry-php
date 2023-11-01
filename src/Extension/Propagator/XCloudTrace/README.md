[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/extension-propagator-cloudtrace/releases)
[![Source](https://img.shields.io/badge/source-extension--propagator--xcloudtrace-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/Extension/Propagator/XCloudTrace)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:extension--propagator--xcloudtrace-blue)](https://github.com/opentelemetry-php/extension-propagator-cloudtrace)
[![Latest Version](http://poser.pugx.org/open-telemetry/extension-propagator-cloudtrace/v/unstable)](https://packagist.org/packages/open-telemetry/extension-propagator-cloudtrace/)
[![Stable](http://poser.pugx.org/open-telemetry/extension-propagator-cloudtrace/v/stable)](https://packagist.org/packages/open-telemetry/extension-propagator-cloudtrace/)

# OpenTelemetry Extension
### XCloudTrace Propagator

XCloudTrace is a propagator that supports the specification for the header "x-cloud-trace-context" used for trace context propagation across
service boundaries. (https://cloud.google.com/trace/docs/setup#force-trace). OpenTelemetry PHP XCloudTrace Propagator Extension provides
option to use it bi-directionally or one-way. One-way does not inject the header for downstream consumption, it only processes the incoming headers
and returns the correct span context. It only attaches to existing X-Cloud-Trace-Context traces and does not create downstream ones.
For one-way XCloudTrace:
```text
XCloudTracePropagator::getOneWayInstance()
```

For bi-directional XCloudTrace:
```text
XCloudTracePropagator::getInstance()
```

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
