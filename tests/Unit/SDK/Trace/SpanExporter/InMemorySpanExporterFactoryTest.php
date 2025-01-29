<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemorySpanExporterFactory::class)]
class InMemorySpanExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new InMemorySpanExporterFactory())->create();
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }

    public function test_type(): void
    {
        $factory = new InMemorySpanExporterFactory();
        $this->assertSame('memory', $factory->type());
    }

    public function test_priority(): void
    {
        $factory = new InMemorySpanExporterFactory();
        $this->assertSame(0, $factory->priority());
    }
}
