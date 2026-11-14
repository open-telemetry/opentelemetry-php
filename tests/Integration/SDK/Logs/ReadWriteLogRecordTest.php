<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class ReadWriteLogRecordTest extends TestCase
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#onemit
     */
    #[Group('logs-compliance')]
    public function test_mutations_are_visible_to_next_processor(): void
    {
        $loggerProvider = LoggerProvider::builder()
            ->addLogRecordProcessor(new class() implements LogRecordProcessorInterface {
                #[\Override]
                public function onEmit(ReadWriteLogRecord $record, ?ContextInterface $context = null): void
                {
                    $record
                        ->setBody('updated')
                        ->setEventName('updated.event')
                        ->setAttribute('added', 'value');
                    $record->removeAttribute('removed');
                }

                #[\Override]
                public function shutdown(?CancellationInterface $cancellation = null): bool
                {
                    return true;
                }

                #[\Override]
                public function forceFlush(?CancellationInterface $cancellation = null): bool
                {
                    return true;
                }
            })
            ->addLogRecordProcessor(new SimpleLogRecordProcessor($exporter = new InMemoryExporter()))
            ->build();

        $loggerProvider->getLogger('test')->emit(
            (new LogRecord('original'))->setAttributes(['kept' => 'value', 'removed' => 'value']),
        );

        /** @var list<ReadableLogRecord> $logs */
        $logs = $exporter->getStorage()->getArrayCopy();
        $this->assertCount(1, $logs);
        $record = $logs[0];
        $this->assertSame('updated', $record->getBody());
        $this->assertSame('updated.event', $record->getEventName());
        $this->assertSame(['kept' => 'value', 'added' => 'value'], $record->getAttributes()->toArray());
    }
}
