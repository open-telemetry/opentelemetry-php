<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryExporterFactory::class)]
class InMemoryExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new InMemoryExporterFactory();
        $this->assertInstanceOf(LogRecordExporterInterface::class, $factory->create());
    }
    public function test_type(): void
    {
        $factory = new InMemoryExporterFactory();
        $this->assertSame('memory', $factory->type());
    }

    public function test_priority(): void
    {
        $factory = new InMemoryExporterFactory();
        $this->assertSame(0, $factory->priority());
    }
}
