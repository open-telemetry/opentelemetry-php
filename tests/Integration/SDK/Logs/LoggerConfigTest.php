<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Logs;

use ArrayObject;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class LoggerConfigTest extends TestCase
{
    /**
     * If a Logger is disabled, it MUST behave equivalently to No-op Logger
     */
    #[Group('logs-compliance')]
    public function test_disable_scope_then_enable(): void
    {
        $storage = new ArrayObject([]);
        $exporter = new InMemoryExporter($storage);
        $loggerProvider = LoggerProvider::builder()
            ->addLogRecordProcessor(new SimpleLogRecordProcessor($exporter))
            ->setConfigurator(
                Configurator::logger()
                ->with(static fn (LoggerConfig $config) => $config->setDisabled(true), name: 'two')
            )
            ->build();
        $this->assertInstanceOf(LoggerProvider::class, $loggerProvider);

        $logger_one = $loggerProvider->getLogger('one');
        $logger_two = $loggerProvider->getLogger('two');
        $logger_three = $loggerProvider->getLogger('three');

        $this->assertTrue($logger_one->isEnabled());
        $this->assertFalse($logger_two->isEnabled());
        $this->assertTrue($logger_three->isEnabled());

        $this->assertCount(0, $storage);
        $logger_one->emit(new LogRecord());
        $this->assertCount(1, $storage);
        $logger_two->emit(new LogRecord());
        $this->assertCount(1, $storage, 'no record emitted');

        $loggerProvider->updateConfigurator(Configurator::logger()); //re-enable all
        $this->assertTrue($logger_one->isEnabled());
        $this->assertTrue($logger_two->isEnabled());
        $this->assertTrue($logger_three->isEnabled());

        $logger_one->emit(new LogRecord());
        $this->assertCount(2, $storage);
        $logger_two->emit(new LogRecord());
        $this->assertCount(3, $storage, 'logger enabled, record emitted');
    }
}
