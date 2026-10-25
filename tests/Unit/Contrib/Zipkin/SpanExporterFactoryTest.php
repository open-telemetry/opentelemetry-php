<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Zipkin;

use OpenTelemetry\Contrib\Zipkin\Exporter;
use OpenTelemetry\Contrib\Zipkin\SpanExporterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanExporterFactory::class)]
final class SpanExporterFactoryTest extends TestCase
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk_exporters/zipkin.md
     */
    public function test_create_triggers_deprecation(): void
    {
        $messages = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$messages): bool {
            $messages[] = $errstr;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $exporter = (new SpanExporterFactory())->create();
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(Exporter::class, $exporter);
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Zipkin exporter is deprecated', $messages[0]);
    }
}
