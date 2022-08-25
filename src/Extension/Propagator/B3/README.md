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