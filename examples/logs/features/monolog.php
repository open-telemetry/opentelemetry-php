<?php

declare(strict_types=1);

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use Psr\Log\LogLevel;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=otlp');
putenv('OTEL_LOGS_PROCESSOR=batch');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4318');

require __DIR__ . '/../../../vendor/autoload.php';
$streamHandler = new StreamHandler(STDOUT, LogLevel::DEBUG);

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
            ->setSeverityNumber($this->severityNumber($record['level_name']))
            ->setAttributes($record['context'] + $record['extra']);
    }
    private function severityNumber(string $level): int
    {
        switch (strtolower($level)) {
            case LogLevel::DEBUG:
                return 5;
            case LogLevel::INFO:
                return 9;
            case LogLevel::NOTICE:
                return 10;
            case LogLevel::WARNING:
                return 13;
            case LogLevel::ERROR:
                return 17;
            case LogLevel::CRITICAL:
                return 18;
            case LogLevel::ALERT:
                return 19;
            case LogLevel::EMERGENCY:
                return 20;
            default:
                return 0;
        }
    }
};

$monolog = new Logger('otel-php', [$otelHandler, $streamHandler]);

$monolog->debug('debug message');
$monolog->info('hello world', ['extra_one' => 'value_one']);
$monolog->alert('foo', ['extra_two' => 'value_two']);
