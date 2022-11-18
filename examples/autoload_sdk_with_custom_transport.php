<?php

declare(strict_types=1);

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_METRICS_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=grpc');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch');

echo 'autoloading SDK example starting...' . PHP_EOL;

// Composer autoloader will execute SDK/_autoload.php which will register global instrumentation from environment configuration
require dirname(__DIR__) . '/vendor/autoload.php';

//create a transport factory to override the default grpc one (for both traces and metrics):
$factory = new class() implements \OpenTelemetry\SDK\Common\Export\TransportFactoryInterface {
    public function create(string $endpoint, string $contentType, array $headers = [], $compression = null, float $timeout = 10., int $retryDelay = 100, int $maxRetries = 3, ?string $cacert = null, ?string $cert = null, ?string $key = null): \OpenTelemetry\SDK\Common\Export\TransportInterface
    {
        return new class() implements \OpenTelemetry\SDK\Common\Export\TransportInterface {
            public function contentType(): string
            {
                return 'application/x-protobuf';
            }
            public function send(string $payload, ?\OpenTelemetry\SDK\Common\Future\CancellationInterface $cancellation = null): \OpenTelemetry\SDK\Common\Future\FutureInterface
            {
                return new \OpenTelemetry\SDK\Common\Future\CompletedFuture(true);
            }
            public function shutdown(?\OpenTelemetry\SDK\Common\Future\CancellationInterface $cancellation = null): bool
            {
                return true;
            }
            public function forceFlush(?\OpenTelemetry\SDK\Common\Future\CancellationInterface $cancellation = null): bool
            {
                return true;
            }
        };
    }
};

\OpenTelemetry\SDK\FactoryRegistry::registerTransportFactory('grpc', $factory, true);

$instrumentation = new \OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation('demo');

$instrumentation->tracer()->spanBuilder('root')->startSpan()->end();
$instrumentation->meter()->createCounter('cnt')->add(1);

echo 'Finished!' . PHP_EOL;
