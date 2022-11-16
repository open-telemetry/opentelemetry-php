<?php

declare(strict_types=1);

use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\TracerProvider;

require __DIR__ . '/../vendor/autoload.php';

//@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration
putenv('OTEL_SDK_DISABLED=true');

echo 'Creating (disabled) signals' . PHP_EOL;

//trace
$tracer = (new TracerProvider())->getTracer('io.opentelemetry.contrib.php');
var_dump(get_class($tracer));

//metrics
$clock = ClockFactory::getDefault();
$reader = new ExportingReader(new MetricExporter((new StreamTransportFactory())->create(STDOUT, 'application/x-ndjson')), $clock);
$views = new CriteriaViewRegistry();
$meterProvider = new MeterProvider(
    null,
    ResourceInfoFactory::emptyResource(),
    $clock,
    Attributes::factory(),
    new InstrumentationScopeFactory(Attributes::factory()),
    [$reader],
    $views,
    new WithSampledTraceExemplarFilter(),
    new ImmediateStalenessHandlerFactory(),
);
$meter = $meterProvider->getMeter('io.opentelemetry.contrib.php');
var_dump(get_class($meter));
