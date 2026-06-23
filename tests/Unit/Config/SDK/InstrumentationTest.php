<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK;

use OpenTelemetry\Config\SDK\Instrumentation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Instrumentation::class)]
final class InstrumentationTest extends TestCase
{
    public function test_parse_file_with_valid_yaml(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile, "file_format: '0.4'\n");

        try {
            $instrumentation = Instrumentation::parseFile($tmpFile);

            $this->assertInstanceOf(Instrumentation::class, $instrumentation);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function test_parse_file_with_multiple_files(): void
    {
        $tmpFile1 = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        $tmpFile2 = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile1, "file_format: '0.4'\n");
        file_put_contents($tmpFile2, "file_format: '0.4'\n");

        try {
            $instrumentation = Instrumentation::parseFile([$tmpFile1, $tmpFile2]);

            $this->assertInstanceOf(Instrumentation::class, $instrumentation);
        } finally {
            @unlink($tmpFile1);
            @unlink($tmpFile2);
        }
    }

    public function test_create_returns_configuration_registry(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile, "file_format: '0.4'\n");

        try {
            $instrumentation = Instrumentation::parseFile($tmpFile);
            $result = $instrumentation->create();

            $this->assertInstanceOf(\OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry::class, $result);
        } finally {
            @unlink($tmpFile);
        }
    }
}
