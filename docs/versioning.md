### Assumptions
This project uses [semver v2](https://semver.org/), as does the rest of OpenTelemetry.

### PHP version support
A PHP version is supported until 12 months after its security support ends, according to
https://www.php.net/supported-versions.php. The first release after that date raises the minimum PHP version in a
minor release, and the change is listed under `Breaking changes` in the changelogs. Composer does not install the new
release on an older PHP, while the releases already published stay installable and keep working there. The generated `SemConv` and `proto` packages may keep an older minimum.

A new PHP version runs as experimental in CI before it is released, and becomes supported (and blocking) in the first
release after its general availability.

<!-- php-support-table:start -->
| PHP | Security support ends | Supported until |
|-----|-----------------------|-----------------|
| 8.2 | 2026-12-31 | 2027-12-31 |
| 8.3 | 2027-12-31 | 2028-12-31 |
| 8.4 | 2028-12-31 | 2029-12-31 |
| 8.5 | 2029-12-31 | 2030-12-31 |
<!-- php-support-table:end -->

The dates are generated from [php.watch](https://php.watch/api/v1/versions) by `script/php-version-support.sh --write`.
The `PHP version support` workflow runs monthly, and opens an issue when a version is due to be dropped or added.
`script/php-version-support.sh --check` tells whether the generated text is out of date.

See the specification on [language version support](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/versioning-and-stability.md#language-version-support).

### Goals
#### API Stability:
* Once the API for a given signal (spans, logs, metrics, baggage) has been officially released, that API module will function with any SDK that has the same major version and an equal or greater minor version.
  * example: libraries that are instrumented with opentelemetry-api-trace:1.0.1 will function with SDK library opentelemetry-sdk-trace:1.11.33.
* No existing method names or signatures will change with a patch release.
* Method signatures may only change in a backwards compatible way with a minor release.

#### SDK Stability:
* Public portions of the SDK (constructors, configuration, end-user interfaces) must remain backwards compatible.
* No existing public method names or signatures will change with a patch release.
* Public method signatures may only change in a backwards compatible way with a minor release.

### Methods
#### Mature signals
* Public portions of the SDK (constructors, configuration, end-user interfaces) must remain backwards compatible.
* Internal interfaces are allowed to break.
* API modules for mature (i.e. released) signals will be transitive dependencies of the OpenTelemetry API class.
* Methods for accessing mature APIs will be added, as appropriate to the OpenTelemetry API interface.
* SDK modules for mature (i.e. released) signals will be transitive dependencies of the SDK.
* Configuration options for the SDK modules for mature signals will be exposed, as appropriate, on the OpenTelemetry SDK class.
 
#### Immature or experimental signals
* API namespaces for immature signals will not be transitive dependencies of the API class.
* API namespaces will be named with an "-experimental" suffix to make it abundantly clear that depending on them is at your own risk.
* The classes for immature APIs will be used as if they were mature signals. This will enable users to easily transition from immature to mature usage, without having to change imports.
* SDK classes for immature signals will also be named with an "-experimental" suffix, in parallel to their API modules.
