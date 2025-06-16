<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class LoggerTest extends TestCase
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.40.0/specification/logs/sdk.md#onemit
     */
    #[Group('logs-compliance')]
    public function test_log_record_mutations_visible_to_later_processors(): void
    {
        $logRecord = (new LogRecord())
            ->setAttributes(['foo' => 'bar']);
        $storage = new \ArrayObject();
        $exporter = new InMemoryExporter($storage);
        $mutator = new class($exporter) implements LogRecordProcessorInterface {
            public function __construct(private readonly InMemoryExporter $exporter)
            {
            }

            public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void
            {
                $record->setAttributes(['baz' => 'bat']);
                $this->exporter->export([$record]);
            }

            public function shutdown(?CancellationInterface $cancellation = null): bool
            {
                return true;
            }

            public function forceFlush(?CancellationInterface $cancellation = null): bool
            {
                return true;
            }

            public function isEnabled(ContextInterface $context, InstrumentationScopeInterface $scope, int $severityNumber, string $eventName): bool
            {
                return true;
            }
        };
        $multi = new MultiLogRecordProcessor([
            new SimpleLogRecordProcessor($exporter),
            $mutator,
            new SimpleLogRecordProcessor($exporter),
        ]);
        $logger = LoggerProvider::builder()->addLogRecordProcessor($multi)->build()->getLogger('test');

        $this->assertCount(0, $storage);
        $logger->emit($logRecord);
        $this->assertCount(3, $storage);

        $first = $storage[0]; //@var array $first
        $this->assertSame(['foo' => 'bar'], $first['attributes'], 'original attributes'); //@phpstan-ignore-line

        $second = $storage[1]; //@var array $second
        $this->assertSame(['foo' => 'bar', 'baz' => 'bat'], $second['attributes'], 'mutated attributes'); //@phpstan-ignore-line

        $third = $storage[2]; //@var array $third
        $this->assertSame(['foo' => 'bar', 'baz' => 'bat'], $third['attributes'], 'attributes after mutation by second processor'); //@phpstan-ignore-line
    }
}
