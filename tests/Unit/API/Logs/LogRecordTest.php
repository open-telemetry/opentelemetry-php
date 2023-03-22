<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Logs\LogRecord
 */
class LogRecordTest extends TestCase
{
    /**
     * @dataProvider settersProvider
     */
    public function test_setters(string $method, string $propertyName, $value): void
    {
        $record = new LogRecord();
        $record->{$method}($value);

        $reflection = new \ReflectionClass($record);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $this->assertSame($value, $property->getValue($record));
    }

    public static function settersProvider(): array
    {
        return [
            ['setBody', 'body', 'foo'],
            ['setAttributes', 'attributes', ['foo' => 'bar']],
            ['setSeverityNumber', 'severityNumber', 5],
            ['setSeverityText', 'severityText', 'info'],
            ['setObservedTimestamp', 'observedTimestamp', 999],
            ['setTimestamp', 'timestamp', 888],
            ['setContext', 'context', Context::getCurrent()],
        ];
    }
}
