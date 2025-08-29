<?php

declare(strict_types=1);

namespace final OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

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
}
