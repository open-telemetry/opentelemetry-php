<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor::class)]
class MultiLogRecordProcessorTest extends MockeryTestCase
{
    private array $processors;
    private MultiLogRecordProcessor $multi;

    public function setUp(): void
    {
        $this->processors = [
            $this->createMock(LogRecordProcessorInterface::class),
            $this->createMock(LogRecordProcessorInterface::class),
        ];
        $this->multi = new MultiLogRecordProcessor($this->processors);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('methodProvider')]
    public function test_method_calls_method_on_all_processors(string $method, object $param): void
    {
        //$record = $this->createMock(ReadWriteLogRecord::class);
        foreach ($this->processors as $processor) {
            $processor->expects($this->once())->method($method)->with($this->equalTo($param));
        }
        $this->multi->{$method}($param);
    }

    public static function methodProvider(): array
    {
        return [
            'onEmit' => ['onEmit', Mockery::mock(ReadWriteLogRecord::class)],
            'shutdown' => ['shutdown', Mockery::mock(CancellationInterface::class)],
            'forceFlush' => ['forceFlush', Mockery::mock(CancellationInterface::class)],
        ];
    }
}
