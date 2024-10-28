<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;

ServiceLoader::register(SpanExporterFactoryInterface::class, ConsoleSpanExporterFactory::class);
ServiceLoader::register(SpanExporterFactoryInterface::class, InMemorySpanExporterFactory::class);

ServiceLoader::register(TransportFactoryInterface::class, StreamTransportFactory::class);
ServiceLoader::register(TransportFactoryInterface::class, PsrTransportFactory::class);
