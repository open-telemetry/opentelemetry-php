<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvResource;
use OpenTelemetry\Config\SDK\Configuration\Internal\TrackingEnvReader;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrackingEnvReader::class)]
final class TrackingEnvReaderTest extends TestCase
{
    public function test_read_delegates_to_inner_reader(): void
    {
        $inner = $this->createMock(EnvReader::class);
        $inner->method('read')->with('MY_VAR')->willReturn('value');

        $tracker = new TrackingEnvReader($inner);
        $this->assertSame('value', $tracker->read('MY_VAR'));
    }

    public function test_read_returns_null_when_inner_returns_null(): void
    {
        $inner = $this->createMock(EnvReader::class);
        $inner->method('read')->willReturn(null);

        $tracker = new TrackingEnvReader($inner);
        $this->assertNull($tracker->read('MISSING'));
    }

    public function test_read_tracks_resource_when_resource_collection_set(): void
    {
        $inner = $this->createMock(EnvReader::class);
        $inner->method('read')->with('MY_VAR')->willReturn('value');

        $resources = $this->createMock(ResourceCollection::class);
        $resources->expects($this->once())
            ->method('addResource')
            ->with($this->callback(function (EnvResource $resource): bool {
                return $resource->name === 'MY_VAR' && $resource->value === 'value';
            }));

        $tracker = new TrackingEnvReader($inner);
        $tracker->trackResources($resources);
        $tracker->read('MY_VAR');
    }

    public function test_read_tracks_null_value_resource(): void
    {
        $inner = $this->createMock(EnvReader::class);
        $inner->method('read')->willReturn(null);

        $resources = $this->createMock(ResourceCollection::class);
        $resources->expects($this->once())
            ->method('addResource')
            ->with($this->callback(function (EnvResource $resource): bool {
                return $resource->name === 'MISSING' && $resource->value === null;
            }));

        $tracker = new TrackingEnvReader($inner);
        $tracker->trackResources($resources);
        $tracker->read('MISSING');
    }

    public function test_read_does_not_track_when_resources_null(): void
    {
        $inner = $this->createMock(EnvReader::class);
        $inner->method('read')->willReturn('value');

        $tracker = new TrackingEnvReader($inner);
        // No resources set, should not throw
        $tracker->read('MY_VAR');

        $this->assertSame('value', $tracker->read('MY_VAR'));
    }

    public function test_track_resources_can_be_reset_to_null(): void
    {
        $inner = $this->createMock(EnvReader::class);
        $inner->method('read')->willReturn('value');

        $resources = $this->createMock(ResourceCollection::class);
        $resources->expects($this->once())->method('addResource');

        $tracker = new TrackingEnvReader($inner);
        $tracker->trackResources($resources);
        $tracker->read('VAR1');

        $tracker->trackResources(null);
        // This read should not track
        $tracker->read('VAR2');
    }
}
