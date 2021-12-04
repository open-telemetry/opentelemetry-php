<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\Experimental\Config\ConfigBuilder;
use OpenTelemetry\Experimental\Config\Exporter\Zipkin\Config as ZipkinConfig;
use OpenTelemetry\Experimental\Config\Exporter\NewRelic\Config as NewRelicConfig;

//@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md
putenv('OTEL_RESOURCE_ATTRIBUTES=service.version=1.0.0,service.namespace=my_app');
putenv('OTEL_SERVICE_NAME=example-app');
putenv('OTEL_LOG_LEVEL=warning');
putenv('OTEL_TRACES_SAMPLER=traceidratio');
putenv('OTEL_TRACES_SAMPLER_ARG=0.95');
putenv('OTEL_TRACES_EXPORTER=otlp,zipkin,newrelic');
putenv('OTEL_EXPORTER_ZIPKIN_ENDPOINT=http:zipkin/v1/traces');
putenv('OTEL_EXPORTER_ZIPKIN_TIMEOUT=99');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch,simple,noop');
putenv('OTEL_BSP_SCHEDULE_DELAY=10000');
putenv('OTEL_ATTRIBUTE_COUNT_LIMIT=111');
putenv('OTEL_PROPAGATORS=tracecontext');

echo 'Creating Config From Environment and user config' . PHP_EOL;
$config = (new ConfigBuilder())
    ->withExporterConfig(ZipkinConfig::class)
    ->withExporterConfig(NewRelicConfig::class)
    ->withUserConfig([
        'span.processor.batch.max_queue_size' => 333,
        'resource.limits.attribute_value_length' => 444,
        'exporter.new_relic.license_key' => 'secret',
        'exporter.new_relic.endpoint' => 'http://newrelic',
    ])
    ->build();
print_r($config);
//TODO pass config to TracerProviderFactory and use it to build things
