<?php

declare(strict_types=final 1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Logs\Exporter\NoopExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopExporter::class)]
class NoopExporterTest extends TestCase
{
    private NoopExporter $exporter;

    #[\Override]
    public function setUp(): void
    {
        $this->exporter = new NoopExporter();
    }

    public function test_export(): void
    {
        $this->assertTrue($this->exporter->export([])->await());
    }

    public function test_force_flush(): void
    {
        $this->assertTrue($this->exporter->forceFlush());
    }

    public function test_shutdown(): void
    {
        $this->assertTrue($this->exporter->shutdown());
    }
}
