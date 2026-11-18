<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\SDK\RegistrarInterface;
use OpenTelemetry\SDK\SdkRegistrar;

ServiceLoader::register(RegistrarInterface::class, SdkRegistrar::class);
