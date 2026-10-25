<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use ArrayObject;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordBuilder;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(LogRecordBuilder::class)]
final class LogRecordBuilderTest extends TestCase
{
    private ArrayObject $storage;
    private LoggerInterface $logger;

    #[\Override]
    public function setUp(): void
    {
        $this->storage = new ArrayObject();
        $this->logger = LoggerProvider::builder()
            ->addLogRecordProcessor(new SimpleLogRecordProcessor(new InMemoryExporter($this->storage)))
            ->build()
            ->getLogger('test');
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#emit-a-logrecord
     */
    public function test_set_exception_sets_exception_attributes(): void
    {
        $this->logger->logRecordBuilder()
            ->setException(new RuntimeException('boom'))
            ->emit();

        $attributes = $this->emittedAttributes();
        $this->assertSame('boom', $attributes->get('exception.message'));
        $this->assertSame(RuntimeException::class, $attributes->get('exception.type'));
        $this->assertIsString($attributes->get('exception.stacktrace'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#emit-a-logrecord
     */
    public function test_attributes_set_before_exception_take_precedence(): void
    {
        $this->logger->logRecordBuilder()
            ->setAttribute('exception.message', 'user message')
            ->setAttributes(['exception.type' => 'UserType'])
            ->setException(new RuntimeException('boom'))
            ->emit();

        $attributes = $this->emittedAttributes();
        $this->assertSame('user message', $attributes->get('exception.message'));
        $this->assertSame('UserType', $attributes->get('exception.type'));
        $this->assertIsString($attributes->get('exception.stacktrace'));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#emit-a-logrecord
     */
    public function test_attributes_set_after_exception_take_precedence(): void
    {
        $this->logger->logRecordBuilder()
            ->setException(new RuntimeException('boom'))
            ->setAttribute('exception.message', 'user message')
            ->emit();

        $this->assertSame('user message', $this->emittedAttributes()->get('exception.message'));
    }

    public function test_later_exception_replaces_earlier_exception(): void
    {
        $this->logger->logRecordBuilder()
            ->setException(new RuntimeException('first'))
            ->setException(new RuntimeException('second'))
            ->emit();

        $this->assertSame('second', $this->emittedAttributes()->get('exception.message'));
    }

    private function emittedAttributes(): AttributesInterface
    {
        $this->assertCount(1, $this->storage);
        $record = $this->storage[0];
        $this->assertInstanceOf(ReadableLogRecord::class, $record);

        return $record->getAttributes();
    }
}
