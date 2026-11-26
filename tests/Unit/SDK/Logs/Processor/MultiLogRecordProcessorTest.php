<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\ExtendedLogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(MultiLogRecordProcessor::class)]
class MultiLogRecordProcessorTest extends MockeryTestCase
{
    private array $processors;
    private MultiLogRecordProcessor $multi;

    #[\Override]
    public function setUp(): void
    {
        $this->processors = [
            $this->createMock(LogRecordProcessorInterface::class),
            $this->createMock(LogRecordProcessorInterface::class),
        ];
        $this->multi = new MultiLogRecordProcessor($this->processors);
    }

    #[DataProvider('methodProvider')]
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#enabled
     */
    #[Group('logs-compliance')]
    public function test_is_not_enabled_without_processors(): void
    {
        $this->assertFalse($this->isEnabled(new MultiLogRecordProcessor([])));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#enabled
     */
    #[Group('logs-compliance')]
    public function test_is_not_enabled_when_all_processors_are_disabled(): void
    {
        $this->assertFalse($this->isEnabled(new MultiLogRecordProcessor([
            $this->createExtendedProcessor(false),
            $this->createExtendedProcessor(false),
        ])));
    }

    public function test_is_enabled_when_any_processor_is_enabled(): void
    {
        $this->assertTrue($this->isEnabled(new MultiLogRecordProcessor([
            $this->createExtendedProcessor(false),
            $this->createExtendedProcessor(true),
        ])));
    }

    public function test_processor_without_is_enabled_counts_as_enabled(): void
    {
        $this->assertTrue($this->isEnabled(new MultiLogRecordProcessor([
            $this->createExtendedProcessor(false),
            $this->createStub(LogRecordProcessorInterface::class),
        ])));
    }

    public function test_is_enabled_passes_parameters_to_processors(): void
    {
        $context = Context::getCurrent();
        $scope = $this->createStub(InstrumentationScopeInterface::class);
        $processor = $this->createMock(ExtendedLogRecordProcessorInterface::class);
        $processor->expects($this->once())->method('isEnabled')->with($context, $scope, 9, 'event')->willReturn(true);

        $this->assertTrue((new MultiLogRecordProcessor([$processor]))->isEnabled($context, $scope, 9, 'event'));
    }

    private function createExtendedProcessor(bool $enabled): ExtendedLogRecordProcessorInterface
    {
        $processor = $this->createStub(ExtendedLogRecordProcessorInterface::class);
        $processor->method('isEnabled')->willReturn($enabled);

        return $processor;
    }

    private function isEnabled(MultiLogRecordProcessor $processor): bool
    {
        return $processor->isEnabled(
            Context::getCurrent(),
            $this->createStub(InstrumentationScopeInterface::class),
            null,
            null,
        );
    }
}
