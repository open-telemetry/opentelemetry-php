<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK;

use OpenTelemetry\Config\SDK\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function test_parse_file_with_valid_yaml(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile, "file_format: '1.0-rc.2'\n");

        try {
            $configuration = Configuration::parseFile($tmpFile);

            $this->assertInstanceOf(Configuration::class, $configuration);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function test_parse_file_with_multiple_files(): void
    {
        $tmpFile1 = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        $tmpFile2 = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile1, "file_format: '1.0-rc.2'\n");
        file_put_contents($tmpFile2, "file_format: '1.0-rc.2'\n");

        try {
            $configuration = Configuration::parseFile([$tmpFile1, $tmpFile2]);

            $this->assertInstanceOf(Configuration::class, $configuration);
        } finally {
            @unlink($tmpFile1);
            @unlink($tmpFile2);
        }
    }
}
