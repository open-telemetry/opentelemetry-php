<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\NullSpanConverter;
use PHPUnit\Framework\TestCase;

class NullSpanConverterTest extends TestCase
{
    public function testImplementsSpanConverterInterface(): void
    {
        $this->assertInstanceOf(
            SpanConverterInterface::class,
            new NullSpanConverter()
        );
    }

    public function testConvert(): void
    {
        $this->assertSame(
            [],
            (new NullSpanConverter())->convert(
                $this->createMock(SpanDataInterface::class)
            )
        );
    }
}
