<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\SDK\RegistrarInterface;
use OpenTelemetry\SDK\SdkRegistrar;

// @phan-suppress-next-line PhanTemplateTypeConstraintViolation phan reads `P_ of S_` as a class named S_
ServiceLoader::register(RegistrarInterface::class, SdkRegistrar::class);
