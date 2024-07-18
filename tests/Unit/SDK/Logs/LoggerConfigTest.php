<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use ArrayObject;
use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use OpenTelemetry\SDK\Logs\LoggerConfigurator;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoggerConfig::class)]
#[CoversClass(LoggerConfigurator::class)]
class LoggerConfigTest extends TestCase
{
    public function test_foo(): void
    {
        $storage = new ArrayObject([]);
        $exporter = new InMemoryExporter($storage);
        $loggerProvider = LoggerProvider::builder()
            ->addLogRecordProcessor(new SimpleLogRecordProcessor($exporter))
            ->addLoggerConfiguratorCondition(new Condition(new Name('~two~'), State::DISABLED)) //disable logger named 'two'
            ->build();

        $logger_one = $loggerProvider->getLogger('one');
        $logger_two = $loggerProvider->getLogger('two');
        $logger_three = $loggerProvider->getLogger('three');

        $this->assertNotInstanceOf(NoopLogger::class, $logger_one);
        $this->assertInstanceOf(NoopLogger::class, $logger_two);
        $this->assertNotInstanceOf(NoopLogger::class, $logger_three);
    }
}
