<?php

declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\Otlp\Exporter as OTLPExporter;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

LoggerHolder::set(new Logger('otlp-example', [new StreamHandler('php://stderr')]));

$transport = (new OtlpHttpTransportFactory())->create('http://collector:4318');
$exporter = new OTLPExporter($transport, Protocols::HTTP_PROTOBUF);

echo 'Starting OTLP example';

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $exporter
    )
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$root = $span = $tracer->spanBuilder('root')->startSpan();
$scope = $span->activate();

for ($i = 0; $i < 3; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('loop-' . $i)->startSpan();

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ]);
    $span->addEvent('generated_session', [
        'id' => md5((string) microtime(true)),
    ]);

    $span->end();
}
$root->end();
$scope->detach();
echo PHP_EOL . 'OTLP example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
