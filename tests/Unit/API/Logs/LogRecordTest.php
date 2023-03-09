<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Logs\LogRecord
 */
class LogRecordTest extends TestCase
{
    public function test_setters(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $record = new LogRecord('body');
        $record
            ->setAttributes(['foo' => 'bar'])
            ->setSeverityNumber(5)
            ->setSeverityText('info')
            ->setObservedTimestamp(999)
            ->setContext($context);

        $data = $record->toLogRecordData();
        $this->assertSame('body', $data->data['body']);
    }
}
