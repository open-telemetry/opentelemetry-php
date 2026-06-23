<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Loader;

use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Loader\ConfigurationLoader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlSymfonyFileLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

#[CoversClass(YamlSymfonyFileLoader::class)]
final class YamlSymfonyFileLoaderTest extends TestCase
{
    private function createLoader(?ConfigurationLoader $configuration = null): YamlSymfonyFileLoader
    {
        $configuration ??= $this->createMock(ConfigurationLoader::class);
        $locator = new FileLocator();

        return new YamlSymfonyFileLoader($configuration, $locator);
    }

    public function test_supports_yaml_extension(): void
    {
        $loader = $this->createLoader();

        $this->assertTrue($loader->supports('config.yaml'));
        $this->assertTrue($loader->supports('config.yml'));
    }

    public function test_supports_with_explicit_type(): void
    {
        $loader = $this->createLoader();

        $this->assertTrue($loader->supports('config.txt', 'yaml'));
        $this->assertTrue($loader->supports('config.txt', 'yml'));
    }

    public function test_does_not_support_non_yaml_extension(): void
    {
        $loader = $this->createLoader();

        $this->assertFalse($loader->supports('config.json'));
        $this->assertFalse($loader->supports('config.xml'));
        $this->assertFalse($loader->supports('config.txt'));
    }

    public function test_does_not_support_non_string_resource(): void
    {
        $loader = $this->createLoader();

        $this->assertFalse($loader->supports(123));
        $this->assertFalse($loader->supports(null));
        $this->assertFalse($loader->supports(['config.yaml']));
    }

    public function test_load_parses_yaml_file(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile, "key: value\nlist:\n  - item1\n  - item2\n");

        try {
            $configuration = $this->createMock(ConfigurationLoader::class);
            $configuration->expects($this->once())
                ->method('loadConfiguration')
                ->with(['key' => 'value', 'list' => ['item1', 'item2']]);
            $configuration->expects($this->once())
                ->method('addResource')
                ->with($this->isInstanceOf(FileResource::class));

            $loader = $this->createLoader($configuration);
            $result = $loader->load($tmpFile);

            $this->assertNull($result);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function test_load_throws_on_invalid_yaml(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'otel_test_') . '.yaml';
        file_put_contents($tmpFile, "invalid: yaml: content: [\n");

        try {
            $loader = $this->createLoader();

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessageMatches('/does not contain valid YAML/');

            $loader->load($tmpFile);
        } finally {
            @unlink($tmpFile);
        }
    }
}
