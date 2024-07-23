<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\LogRecord;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=console');
putenv('OTEL_PROPAGATORS=tracecontext');
putenv('OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN=true');

//Usage: php -S localhost:8080 examples/traces/features/auto_root_span.php

require dirname(__DIR__, 3) . '/vendor/autoload.php';

Globals::loggerProvider()->getLogger('test')->emit(new LogRecord('I processed a request'));
echo 'hello world!' . PHP_EOL;
