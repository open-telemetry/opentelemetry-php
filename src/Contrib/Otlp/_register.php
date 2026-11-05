<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Contrib\Otlp\Registrar;
use OpenTelemetry\SDK\RegistrarInterface;

ServiceLoader::register(RegistrarInterface::class, Registrar::class);
