### Assumptions
This project uses [semver v2](https://semver.org/), as does the rest of OpenTelemetry.

### Goals
#### API Stability:
* Once the API for a given signal (spans, logs, metrics, baggage) has been officially released, that API module will function with any SDK that has the same major version and an equal or greater minor version.
  * example: libraries that are instrumented with opentelemetry-api-trace:1.0.1 will function with SDK library opentelemetry-sdk-trace:1.11.33.
* No existing method names or signatures will change with patch versioning.  We will only change signatures in a backwards compatible way with minor versioning.

#### SDK Stability:
* Public portions of the SDK (constructors, configuration, end-user interfaces) must remain backwards compatible.
* Internal interfaces are allowed to break.
* No existing method names or signatures will change with patch versioning.  We will only change signatures in a backwards compatible way with minor versioning.

### Methods
#### Mature signals
* Public portions of the SDK (constructors, configuration, end-user interfaces) must remain backwards compatible.
* Internal interfaces are allowed to break.
* API modules for mature (i.e. released) signals will be transitive dependencies of the OpenTelemetry API class.
* Methods for accessing mature APIs will be added, as appropriate to the OpenTelemetry interface.
* SDK modules for mature (i.e. released) signals will be transitive dependencies of the SDK.
* Configuration options for the SDK modules for mature signals will be exposed, as appropriate, on the OpenTelemetry SDK class.
 
#### Immature or experimental signals
* API namespaces for immature signals will not be transitive dependencies of the API class.
* API namespaces will be named with an "-experimental" suffix to make it abundantly clear that depending on them is at your own risk.
* API namespaces for immature signals will be co-versioned along with mature API namespaces.
* The classes for immature APIs will be used as if they were mature signals. This will enable users to easily transition from immature to mature usage, without having to change imports.
* SDK classes for immature signals will also be named with an "-experimental" suffix, in parallel to their API modules.
