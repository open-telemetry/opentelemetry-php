<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Extension\Propagator\B3\Registrar;
use OpenTelemetry\SDK\RegistrarInterface;

if (!class_exists(ServiceLoader::class)) {
    return;
}

// @phan-suppress-next-line PhanTemplateTypeConstraintViolation phan reads `P_ of S_` as a class named S_
ServiceLoader::register(RegistrarInterface::class, Registrar::class);
