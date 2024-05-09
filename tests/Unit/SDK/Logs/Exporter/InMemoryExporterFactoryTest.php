<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory::class)]
class InMemoryExporterFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new InMemoryExporterFactory();
        $this->assertInstanceOf(LogRecordExporterInterface::class, $factory->create());
    }
}
