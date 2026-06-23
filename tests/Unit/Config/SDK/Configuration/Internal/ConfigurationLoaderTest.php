<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationLoader;
use OpenTelemetry\Config\SDK\Configuration\Internal\ResourceCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

#[CoversClass(ConfigurationLoader::class)]
final class ConfigurationLoaderTest extends TestCase
{
    public function test_get_configurations_returns_empty_initially(): void
    {
        $loader = new ConfigurationLoader(null);

        $this->assertSame([], $loader->getConfigurations());
    }

    public function test_load_configuration_stores_configuration(): void
    {
        $loader = new ConfigurationLoader(null);

        $loader->loadConfiguration(['key' => 'value']);

        $this->assertSame([['key' => 'value']], $loader->getConfigurations());
    }

    public function test_load_configuration_stores_multiple_configurations(): void
    {
        $loader = new ConfigurationLoader(null);

        $loader->loadConfiguration(['first' => 1]);
        $loader->loadConfiguration(['second' => 2]);

        $this->assertCount(2, $loader->getConfigurations());
        $this->assertSame([['first' => 1], ['second' => 2]], $loader->getConfigurations());
    }

    public function test_add_resource_delegates_to_resource_collection(): void
    {
        $resourceCollection = new ResourceCollection();
        $loader = new ConfigurationLoader($resourceCollection);
        $resource = new FileResource(__FILE__);

        $loader->addResource($resource);

        $this->assertNotEmpty($resourceCollection->toArray());
    }

    public function test_add_resource_with_null_resource_collection_does_not_throw(): void
    {
        $loader = new ConfigurationLoader(null);
        $resource = new FileResource(__FILE__);

        $loader->addResource($resource);

        // No exception should be thrown
        $this->assertTrue(true);
    }

    public function test_load_configuration_accepts_scalar_values(): void
    {
        $loader = new ConfigurationLoader(null);

        $loader->loadConfiguration('string-value');
        $loader->loadConfiguration(42);
        $loader->loadConfiguration(null);

        $this->assertSame(['string-value', 42, null], $loader->getConfigurations());
    }
}
