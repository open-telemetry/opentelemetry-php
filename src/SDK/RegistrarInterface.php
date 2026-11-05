<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

/**
 * Service provider interface (SPI) through which a package contributes components to the {@see Registry}, for example
 * exporter factories, transport factories and propagators.
 *
 * Implementations are discovered with the SPI service loader and run lazily, the first time the registry is asked for a
 * component. This makes registration independent of the order in which Composer includes the `autoload.files` of the
 * installed packages, which a `_register.php` file that calls the {@see Registry} directly is not.
 *
 * Declare an implementation under `extra.spi` in `composer.json`, or call
 * `\Nevay\SPI\ServiceLoader::register(RegistrarInterface::class, MyRegistrar::class)` from a file listed in
 * `extra.spi-config.autoload-files`.
 */
interface RegistrarInterface
{
    /**
     * Registers components with the {@see Registry}. Registrations must not clobber (the default), so that components
     * registered explicitly by an application or a distribution win. The order in which registrars run is not defined,
     * so a package that must replace a component of another one registers it with `$clobber`.
     */
    public function register(): void;
}
