# Upgrading notes for major versions

## 1.x -> 2.x

### API

### SDK

#### SDK\Registry removed
`SDK\Registry` has been removed, and the technique of registering components (eg propagators, transports,
auto-instrumentations) has been replaced with [Nevay/SPI](https://github.com/Nevay/spi/) ServiceLoader.

The ServiceLoader should be configured through existing `_register.php` files, which should *only* contain calls to
SPI's `ServiceLoader::register()` method.

SPI has a composer plugin which will scan for `ServiceLoader::register()` calls in `autoload.files` and generate a
`GeneratedServiceProviderData.php` file in `vendor/composer/`. The plugin will then remove those `autoload.files` entries
from composer's generated output to avoid double-loading.
Pre-generating the services in this way avoids a race-condition in 1.x where composer's `autoload.files` are executed in an
undefined order, and services may not be registered in time for the SDK to use them.

For SPI to work correctly, the composer plugin _should_ be allowed to run. If the plugin is not allowed to run, then
services will still register at runtime, however this might still suffer from the same race-condition as `1.x`.

#### FactoryInterfaces updated
Various component factory interfaces (eg `TextMapPropagatorFactoryInterface`, `TransportFactoryInterface`) have been
updated to include `priority()` and `type()` methods. These are used in conjunction with SPI ServiceLoader to associate
a type (eg `otlp`) with a factory, and to allow SDK-provided factories to be replaced by user-provided factories (by
providing a higher priority for the same type).
