<?php

declare(strict_types=1);

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Map\Psr3;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use Psr\Log\LogLevel;

/**
 * This example creates a monolog handler which integrates with opentelemetry, as described in:
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md#usage
 */

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=otlp');
putenv('OTEL_LOGS_PROCESSOR=batch');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');

require __DIR__ . '/../../../vendor/autoload.php';
$streamHandler = new StreamHandler(STDOUT, LogLevel::DEBUG);
$tracer = Globals::tracerProvider()->getTracer('monolog-demo');

//otel handler for Monolog v2, which ignores logs < INFO
$otelHandler = new class('demo', 'demo-domain', LogLevel::INFO) extends AbstractProcessingHandler {
    private EventLogger $eventLogger;

    public function __construct(string $name, string $domain, string $level, bool $bubble = true, ?LoggerProviderInterface $loggerProvider = null)
    {
        parent::__construct($level, $bubble);
        $loggerProvider ??= Globals::loggerProvider();
        $this->eventLogger = new EventLogger($loggerProvider->getLogger($name), $domain);
    }

    protected function write(array $record): void
    {
        $this->eventLogger->logEvent('foo', $this->convert($record));
    }

    private function convert(array $record): LogRecord
    {
        return (new LogRecord($record['message']))
            ->setSeverityText($record['level_name'])
            ->setObservedTimestamp($record['datetime']->format('U') * ClockInterface::NANOS_PER_SECOND)
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
