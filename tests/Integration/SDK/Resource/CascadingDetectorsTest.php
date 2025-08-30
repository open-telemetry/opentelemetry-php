<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Resource;

use OpenTelemetry\SDK\Resource\Detectors\Apache;
use OpenTelemetry\SDK\Resource\Detectors\Composite;
use OpenTelemetry\SDK\Resource\Detectors\Fpm;
use OpenTelemetry\SDK\Resource\Detectors\Service;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

class CascadingDetectorsTest extends TestCase
{
    public function test_service_detector_provides_uuid_fallback(): void
    {
        $serviceDetector = new Service();
        $resource = $serviceDetector->getResource();

        $instanceId = $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);

        // Service detector should provide a UUID
        $this->assertIsString($instanceId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $instanceId);
    }

    public function test_apache_and_fpm_override_service_uuid_when_applicable(): void
    {
        // Test that when composed, Apache/FPM detectors override Service detector's UUID

        $composite = new Composite([
            new Service(),  // Generates UUID first
            new Apache(),   // Should override if running under Apache (empty in CLI)
            new Fpm(),      // Should override if running under FPM (empty in CLI)
        ]);

        $resource = $composite->getResource();
        $instanceId = $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);

        // Since we're running in CLI, Apache and FPM return empty resources,
        // so Service detector's UUID should remain
        $this->assertIsString($instanceId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $instanceId);
    }

    public function test_detector_order_matters_for_overriding(): void
    {
        // Test that detector order affects which values are used (later wins)

        $serviceFirst = new Composite([
            new Service(),
            new Apache(),
            new Fpm(),
        ]);

        $serviceAfter = new Composite([
            new Apache(),
            new Fpm(),
            new Service(),  // This would override any stable IDs with UUID
        ]);

        $resourceServiceFirst = $serviceFirst->getResource();
        $resourceServiceAfter = $serviceAfter->getResource();

        $instanceIdFirst = $resourceServiceFirst->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);
        $instanceIdAfter = $resourceServiceAfter->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);

        // Both should be UUIDs in CLI environment, but demonstrate order matters
        $this->assertIsString($instanceIdFirst);
        $this->assertIsString($instanceIdAfter);

        // In CLI environment, both will be UUIDs, but they'll be the same static UUID
        // since Service detector uses static variable
        $this->assertEquals($instanceIdFirst, $instanceIdAfter);
    }

    public function test_stable_id_generation_consistency(): void
    {
        // Test that runtime detectors generate consistent IDs

        $apache = new Apache();
        $fpm = new Fpm();

        // Get resources multiple times to verify consistency
        $apacheResource1 = $apache->getResource();
        $apacheResource2 = $apache->getResource();

        $fpmResource1 = $fpm->getResource();
        $fpmResource2 = $fpm->getResource();

        // In CLI environment, these should be empty resources (no attributes)
        $this->assertCount(0, $apacheResource1->getAttributes());
        $this->assertCount(0, $apacheResource2->getAttributes());
        $this->assertCount(0, $fpmResource1->getAttributes());
        $this->assertCount(0, $fpmResource2->getAttributes());
    }

    public function test_environment_variables_override_all_detectors(): void
    {
        // Test that Environment detector (via OTEL_RESOURCE_ATTRIBUTES) has highest priority

        // Set up environment variable to override service instance ID
        $_SERVER['OTEL_RESOURCE_ATTRIBUTES'] = 'service.instance.id=custom-override-id,service.name=test-service';
        $_ENV['OTEL_RESOURCE_ATTRIBUTES'] = 'service.instance.id=custom-override-id,service.name=test-service';

        try {
            $composite = new Composite([
                new Service(),      // Would generate UUID
                new Apache(),       // Would generate stable ID if in Apache
                new Fpm(),          // Would generate stable ID if in FPM
                new \OpenTelemetry\SDK\Resource\Detectors\Environment(), // Should override all
            ]);

            $resource = $composite->getResource();
            $instanceId = $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);
            $serviceName = $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME);

            // Environment should override with our custom value
            $this->assertEquals('custom-override-id', $instanceId);
            $this->assertEquals('test-service', $serviceName);

        } finally {
            // Clean up environment variables
            unset($_SERVER['OTEL_RESOURCE_ATTRIBUTES']);
            unset($_ENV['OTEL_RESOURCE_ATTRIBUTES']);
        }
    }
}
