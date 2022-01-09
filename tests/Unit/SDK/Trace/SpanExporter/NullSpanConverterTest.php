<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\NullSpanConverter;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\SpanExporter\NullSpanConverter
 */
class NullSpanConverterTest extends TestCase
{
    public function test_convert(): void
    {
        $this->assertSame(
            [],
            (new NullSpanConverter())->convert(
                [$this->createMock(SpanDataInterface::class)]
            )[0]
        );
    }
}
