<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\LogRecord;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=otlp');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4318');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf');
putenv('OTEL_PROPAGATORS=tracecontext');
putenv('OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN=true');
putenv('OTEL_PHP_EXPERIMENTAL_SPI_REGISTRY=true');
putenv('OTEL_PHP_DETECTORS=sdk,test');
$_SERVER['REQUEST_METHOD'] = 'GET';

//Usage: php -S localhost:8080 examples/traces/features/auto_root_span.php

require dirname(__DIR__, 3) . '/vendor/autoload.php';

Globals::loggerProvider()->getLogger('test')->emit(new LogRecord('I processed a request'));
echo 'hello world!' . PHP_EOL;
