<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory::class)]
class InMemorySpanExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $exporter = (new InMemorySpanExporterFactory())->create();
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }
}
