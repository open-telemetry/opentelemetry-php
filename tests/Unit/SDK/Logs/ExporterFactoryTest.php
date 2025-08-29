<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;
use OpenTelemetry\SDK\Logs\ExporterFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExporterFactory::class)]
class ExporterFactoryTest extends TestCase
{
    use TestState;

    #[\Override]
    public function setUp(): void
    {
        // Ensure all required factories are registered in the Registry
        $this->ensureRequiredFactoriesRegistered();
    }
    
    private function ensureRequiredFactoriesRegistered(): void
    {
        // Register console log record exporter factory if not already registered
        try {
            \OpenTelemetry\SDK\Registry::logRecordExporterFactory('console');
        } catch (\RuntimeException $e) {
            \OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory('console', \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory::class);
        }
        
        // Register stream transport factory if not already registered
        try {
            \OpenTelemetry\SDK\Registry::transportFactory('stream');
        } catch (\RuntimeException $e) {
            \OpenTelemetry\SDK\Registry::registerTransportFactory('stream', \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory::class);
        }
    }

    /**
     * @param class-string $expected
     */
    #[DataProvider('exporterProvider')]
    public function test_create(string $name, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_LOGS_EXPORTER', $name);
        $exporter = (new ExporterFactory())->create();

        $this->assertInstanceOf($expected, $exporter);
    }

    public static function exporterProvider(): array
    {
        return [
            ['console', ConsoleExporter::class],
            ['none', NoopExporter::class],
        ];
    }

    public function test_rejects_multiple(): void
    {
        $this->setEnvironmentVariable('OTEL_LOGS_EXPORTER', 'one,two');
        $this->expectException(\InvalidArgumentException::class);

        (new ExporterFactory())->create();
    }
}
