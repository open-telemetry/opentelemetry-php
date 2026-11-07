<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Contrib\Grpc\Registrar;
use OpenTelemetry\SDK\RegistrarInterface;

ServiceLoader::register(RegistrarInterface::class, Registrar::class);
