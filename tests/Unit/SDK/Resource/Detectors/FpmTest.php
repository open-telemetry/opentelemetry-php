<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Fpm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Fpm::class)]
class FpmTest extends TestCase
{
    public function test_fmp_returns_empty_for_non_fpm_sapi(): void
    {
        // Since we're not running under FPM SAPI in tests, should return empty resource
        $resourceDetector = new Fpm();
        $resource = $resourceDetector->getResource();

        $this->assertCount(0, $resource->getAttributes());
    }

    public function test_fpm_generates_stable_instance_id(): void
    {
        $resourceDetector = new Fpm();

        // Mock FPM environment by creating a reflection to access private methods
        $reflection = new \ReflectionClass($resourceDetector);
        $getStableInstanceIdMethod = $reflection->getMethod('getStableInstanceId');
        $getStableInstanceIdMethod->setAccessible(true);

        // Call the method twice to ensure it's deterministic
        $instanceId1 = $getStableInstanceIdMethod->invoke($resourceDetector);
        $instanceId2 = $getStableInstanceIdMethod->invoke($resourceDetector);

        $this->assertSame($instanceId1, $instanceId2);
        // Should be a valid UUID format (v5)
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $instanceId1);
    }

    public function test_fpm_pool_name_detection(): void
    {
        $resourceDetector = new Fpm();
        $reflection = new \ReflectionClass($resourceDetector);
        $getFpmPoolNameMethod = $reflection->getMethod('getFpmPoolName');
        $getFpmPoolNameMethod->setAccessible(true);

        // Test with FPM_POOL in $_SERVER
        $_SERVER['FPM_POOL'] = 'test-pool';
        $poolName = $getFpmPoolNameMethod->invoke($resourceDetector);
        $this->assertSame('test-pool', $poolName);
        unset($_SERVER['FPM_POOL']);

        // Test with FPM_POOL in $_ENV
        $_ENV['FPM_POOL'] = 'env-pool';
        $poolName = $getFpmPoolNameMethod->invoke($resourceDetector);
        $this->assertSame('env-pool', $poolName);
        unset($_ENV['FPM_POOL']);

        // Test without FPM_POOL set
        $poolName = $getFpmPoolNameMethod->invoke($resourceDetector);
        $this->assertNull($poolName);
    }
}
