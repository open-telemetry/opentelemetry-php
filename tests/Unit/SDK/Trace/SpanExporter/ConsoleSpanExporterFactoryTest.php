<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleSpanExporterFactory::class)]
class ConsoleSpanExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new ConsoleSpanExporterFactory())->create();
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }

    public function test_type(): void
    {
        $factory = new ConsoleSpanExporterFactory();
        $this->assertSame('console', $factory->type());
    }

    public function test_priority(): void
    {
        $factory = new ConsoleSpanExporterFactory();
        $this->assertSame(0, $factory->priority());
    }
}
