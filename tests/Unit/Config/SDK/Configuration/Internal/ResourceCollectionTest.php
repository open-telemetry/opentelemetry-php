<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Internal\ResourceCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

#[CoversClass(ResourceCollection::class)]
final class ResourceCollectionTest extends TestCase
{
    public function test_to_array_returns_empty_initially(): void
    {
        $collection = new ResourceCollection();

        $this->assertSame([], $collection->toArray());
    }

    public function test_add_resource_adds_file_resource(): void
    {
        $collection = new ResourceCollection();
        $resource = new FileResource(__FILE__);

        $collection->addResource($resource);

        $resources = $collection->toArray();
        $this->assertNotEmpty($resources);
    }

    public function test_add_class_resource_for_existing_class(): void
    {
        $collection = new ResourceCollection();
        $collection->addClassResource(self::class);

        // The class exists and its file is not in vendor, so it should be added
        $resources = $collection->toArray();
        $this->assertNotEmpty($resources);
    }

    public function test_add_resource_deduplicates_by_string_key(): void
    {
        $collection = new ResourceCollection();
        $resource = new FileResource(__FILE__);

        $collection->addResource($resource);
        $collection->addResource($resource);

        $resources = $collection->toArray();
        // Same resource added twice should only appear once
        $this->assertCount(1, $resources);
    }
}
