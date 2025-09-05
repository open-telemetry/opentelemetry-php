<?php

declare(strict_types=1);

namespace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsoleSpanExporterFactory::class)]
class ConsoleSpanExporterFactoryTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        // Ensure required factories are registered in the Registry
        $this->ensureRequiredFactoriesRegistered();
    }

    private function ensureRequiredFactoriesRegistered(): void
    {
        // Register stream transport factory if not already registered
        try {
            \OpenTelemetry\SDK\Registry::transportFactory('stream');
        } catch (\RuntimeException $e) {
            // Stream transport factory not registered, register it
            \OpenTelemetry\SDK\Registry::registerTransportFactory('stream', \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory::class);
        }
    }

    public function test_create(): void
    {
        $exporter = (new ConsoleSpanExporterFactory())->create();
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }
}
