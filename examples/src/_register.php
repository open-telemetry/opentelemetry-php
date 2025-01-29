<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Example\ExampleConfigProvider;
use OpenTelemetry\Example\ExampleInstrumentation;
use OpenTelemetry\Example\TestResourceDetectorFactory;
use OpenTelemetry\SDK\Resource\ResourceDetectorFactoryInterface;

ServiceLoader::register(Instrumentation::class, ExampleInstrumentation::class);
ServiceLoader::register(ResourceDetectorFactoryInterface::class, TestResourceDetectorFactory::class);
ServiceLoader::register(ComponentProvider::class, ExampleConfigProvider::class);
