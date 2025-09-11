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
use RuntimeException;

#[CoversClass(ExporterFactory::class)]
class ExporterFactoryTest extends TestCase
{
    use TestState;

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

    public function test_rejects_invalid(): void
    {
        $this->setEnvironmentVariable('OTEL_LOGS_EXPORTER', 'unknown');
        $this->expectException(RuntimeException::class);

        (new ExporterFactory())->create();
    }
}
