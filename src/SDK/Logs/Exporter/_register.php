<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;

ServiceLoader::register(LogRecordExporterFactoryInterface::class, ConsoleExporterFactory::class);
ServiceLoader::register(LogRecordExporterFactoryInterface::class, InMemoryExporterFactory::class);
