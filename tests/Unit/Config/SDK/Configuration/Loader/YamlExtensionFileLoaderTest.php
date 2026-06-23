<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Loader;

use OpenTelemetry\Config\SDK\Configuration\Loader\YamlExtensionFileLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

#[CoversClass(YamlExtensionFileLoader::class)]
final class YamlExtensionFileLoaderTest extends TestCase
{
    private function createLoader(): YamlExtensionFileLoader
    {
        $configuration = $this->createMock(\OpenTelemetry\Config\SDK\Configuration\Loader\ConfigurationLoader::class);
        $locator = new FileLocator();

        return new YamlExtensionFileLoader($configuration, $locator);
    }

    public function test_supports_yaml_extension(): void
    {
        if (!extension_loaded('yaml')) {
            $this->markTestSkipped('yaml extension is not loaded');
        }

        $loader = $this->createLoader();

        $this->assertTrue($loader->supports('config.yaml'));
        $this->assertTrue($loader->supports('config.yml'));
    }

    public function test_supports_with_explicit_type(): void
    {
        if (!extension_loaded('yaml')) {
            $this->markTestSkipped('yaml extension is not loaded');
        }

        $loader = $this->createLoader();

        $this->assertTrue($loader->supports('config.txt', 'yaml'));
        $this->assertTrue($loader->supports('config.txt', 'yml'));
    }

    public function test_does_not_support_non_yaml_extension(): void
    {
        if (!extension_loaded('yaml')) {
            $this->markTestSkipped('yaml extension is not loaded');
        }

        $loader = $this->createLoader();

        $this->assertFalse($loader->supports('config.json'));
        $this->assertFalse($loader->supports('config.xml'));
        $this->assertFalse($loader->supports('config.txt'));
    }

    public function test_does_not_support_non_string_resource(): void
    {
        if (!extension_loaded('yaml')) {
            $this->markTestSkipped('yaml extension is not loaded');
        }

        $loader = $this->createLoader();

        $this->assertFalse($loader->supports(123));
        $this->assertFalse($loader->supports(null));
        $this->assertFalse($loader->supports(['config.yaml']));
    }

    public function test_does_not_support_without_yaml_extension(): void
    {
        if (extension_loaded('yaml')) {
            $this->markTestSkipped('yaml extension is loaded, cannot test absence');
        }

        $loader = $this->createLoader();

        $this->assertFalse($loader->supports('config.yaml'));
    }
}
