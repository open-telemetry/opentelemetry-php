<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Logs\Bridge;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Map\Psr3;
use Psr\Log\LogLevel;

/**
 * This example creates a monolog handler which integrates with opentelemetry. In this example, the logger is
 * configured from environment, and autoloaded as part of composer where is can be retrieved from `Globals`.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md#usage
 *
 * We have an official monolog handler, @see https://packagist.org/packages/open-telemetry/opentelemetry-logger-monolog
 */

// create env vars before requiring composer
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=otlp');
putenv('OTEL_LOGS_PROCESSOR=batch');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');

require __DIR__ . '/../../../vendor/autoload.php';
$streamHandler = new StreamHandler(STDOUT, LogLevel::DEBUG);
$tracer = Globals::tracerProvider()->getTracer('monolog-demo');

//otel handler for Monolog v2
$otelHandler = new class(LogLevel::INFO) extends AbstractProcessingHandler {
    private LoggerInterface $logger;

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function __construct(string $level, bool $bubble = true, ?LoggerProviderInterface $provider = null)
    {
        parent::__construct($level, $bubble);
        $provider ??= Globals::loggerProvider();
        $this->logger = $provider->getLogger('monolog-demo', null, null, ['logging.library' => 'monolog']);
    }

    protected function write(array $record): void
    {
        $this->logger->emit($this->convert($record));
    }

    private function convert(array $record): LogRecord
    {
        return (new LogRecord($record['message']))
            ->setSeverityText($record['level_name'])
            ->setTimestamp((int) (microtime(true) * LogRecord::NANOS_PER_SECOND))
            ->setObservedTimestamp($record['datetime']->format('U') * LogRecord::NANOS_PER_SECOND)
            ->setSeverityNumber(Psr3::severityNumber($record['level_name']))
            ->setAttributes($record['context'] + $record['extra']);
    }
};

//start a span so that logs contain span context
$span = $tracer->spanBuilder('foo')->startSpan();
$scope = $span->activate();

$monolog = new Logger('otel-php-monolog', [$otelHandler, $streamHandler]);

$monolog->debug('debug message');
$monolog->info('hello world', ['extra_one' => 'value_one']);
$monolog->alert('foo', ['extra_two' => 'value_two']);

$scope->detach();
$span->end();
