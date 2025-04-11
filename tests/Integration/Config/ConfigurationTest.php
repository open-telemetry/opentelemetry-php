<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config;

use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\Configuration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ConfigurationTest extends TestCase
{
    public function setUp(): void
    {
        // set up mock file system with /var/log directory, for otlp_file exporter.
        $root = vfsStream::setup('/', null, ['var' => ['log' => []]])->url();

        OutputStreamParser::setRoot($root);
    }

    public function tearDown(): void
    {
        OutputStreamParser::reset();
    }

    #[DataProvider('openTelemetryConfigurationDataProvider')]
    public function test_open_telemetry_configuration(string $file): void
    {
        $this->expectNotToPerformAssertions();
        Configuration::parseFile($file)->create();
    }

    public static function openTelemetryConfigurationDataProvider(): iterable
    {
        yield 'kitchen-sink' => [__DIR__ . '/configurations/kitchen-sink.yaml'];
        //yield 'anchors' => [__DIR__ . '/configurations/anchors.yaml'];
    }
}
