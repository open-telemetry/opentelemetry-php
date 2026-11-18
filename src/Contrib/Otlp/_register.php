<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Contrib\Otlp\Registrar;
use OpenTelemetry\SDK\RegistrarInterface;

// @phan-suppress-next-line PhanTemplateTypeConstraintViolation phan reads `P_ of S_` as a class named S_
ServiceLoader::register(RegistrarInterface::class, Registrar::class);
