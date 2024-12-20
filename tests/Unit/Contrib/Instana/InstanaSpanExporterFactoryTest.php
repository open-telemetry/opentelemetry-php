<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Instana;

use OpenTelemetry\Contrib\Instana\SpanExporterFactory as InstanaSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstanaSpanExporterFactory::class)]
class InstanaSpanExporterFactoryTest extends TestCase
{
	private InstanaSpanExporterFactory $factory;

    public function setUp(): void
    {
        $this->factory = new InstanaSpanExporterFactory();
    }

    public function test_instana_exporter_create(): void
    {
        $exporter = $this->factory->create();
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }
}
