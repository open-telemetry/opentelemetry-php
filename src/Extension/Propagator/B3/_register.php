<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Extension\Propagator\B3\Registrar;
use OpenTelemetry\SDK\RegistrarInterface;

if (!class_exists(ServiceLoader::class)) {
    return;
}

ServiceLoader::register(RegistrarInterface::class, Registrar::class);
