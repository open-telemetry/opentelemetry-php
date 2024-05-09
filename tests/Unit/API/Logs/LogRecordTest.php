<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\API\Logs\LogRecord::class)]
class LogRecordTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('settersProvider')]
    public function test_setters(string $method, string $propertyName, mixed $value, mixed $expected = null): void
    {
        $record = new LogRecord();
        $record->{$method}($value);

        $reflection = new \ReflectionClass($record);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $this->assertSame($expected ?? $value, $property->getValue($record));
    }

    public static function settersProvider(): array
    {
        return [
            ['setBody', 'body', 'foo'],
            ['setAttributes', 'attributes', ['foo' => 'bar']],
            ['setSeverityNumber', 'severityNumber', 5],
            ['setSeverityNumber', 'severityNumber', Severity::ERROR, Severity::ERROR->value],
            ['setSeverityText', 'severityText', 'info'],
            ['setObservedTimestamp', 'observedTimestamp', 999],
            ['setTimestamp', 'timestamp', 888],
            ['setContext', 'context', Context::getCurrent()],
        ];
    }
}
