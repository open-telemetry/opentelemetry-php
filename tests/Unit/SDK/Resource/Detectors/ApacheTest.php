<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Apache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Apache::class)]
class ApacheTest extends TestCase
{
    public function test_apache_returns_empty_for_non_apache_sapi(): void
    {
        // Since we're not running under Apache SAPI in tests, should return empty resource
        $resourceDetector = new Apache();
        $resource = $resourceDetector->getResource();

        $this->assertCount(0, $resource->getAttributes());
    }

    public function test_apache_generates_stable_instance_id(): void
    {
        $resourceDetector = new Apache();

        // Mock Apache environment by creating a reflection to access private methods
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

    public function test_apache_sapi_detection(): void
    {
        $resourceDetector = new Apache();
        $reflection = new \ReflectionClass($resourceDetector);
        $isApacheSapiMethod = $reflection->getMethod('isApacheSapi');
        $isApacheSapiMethod->setAccessible(true);

        // Test detection logic (will be false in CLI test environment)
        $result = $isApacheSapiMethod->invoke($resourceDetector);
        $this->assertFalse($result);
    }

    public function test_server_name_detection(): void
    {
        $resourceDetector = new Apache();
        $reflection = new \ReflectionClass($resourceDetector);
        $getServerNameMethod = $reflection->getMethod('getServerName');
        $getServerNameMethod->setAccessible(true);

        // Test with SERVER_NAME in $_SERVER
        $_SERVER['SERVER_NAME'] = 'example.com';
        $serverName = $getServerNameMethod->invoke($resourceDetector);
        $this->assertSame('example.com', $serverName);

        // Test with HTTP_HOST fallback
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['HTTP_HOST'] = 'fallback.com';
        $serverName = $getServerNameMethod->invoke($resourceDetector);
        $this->assertSame('fallback.com', $serverName);

        // Test without either set
        unset($_SERVER['HTTP_HOST']);
        $serverName = $getServerNameMethod->invoke($resourceDetector);
        $this->assertNull($serverName);
    }

    public function test_document_root_detection(): void
    {
        $resourceDetector = new Apache();
        $reflection = new \ReflectionClass($resourceDetector);
        $getDocumentRootMethod = $reflection->getMethod('getDocumentRoot');
        $getDocumentRootMethod->setAccessible(true);

        // Test with DOCUMENT_ROOT in $_SERVER
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
        $documentRoot = $getDocumentRootMethod->invoke($resourceDetector);
        $this->assertSame('/var/www/html', $documentRoot);

        // Test without DOCUMENT_ROOT set
        unset($_SERVER['DOCUMENT_ROOT']);
        $documentRoot = $getDocumentRootMethod->invoke($resourceDetector);
        $this->assertNull($documentRoot);
    }

    public function test_extract_apache_version_number(): void
    {
        $resourceDetector = new Apache();
        $reflection = new \ReflectionClass($resourceDetector);
        $extractVersionMethod = $reflection->getMethod('extractApacheVersionNumber');
        $extractVersionMethod->setAccessible(true);

        // Test typical Apache version strings
        $this->assertEquals('2.4.41', $extractVersionMethod->invoke($resourceDetector, 'Apache/2.4.41 (Ubuntu)'));
        $this->assertEquals('2.2.34', $extractVersionMethod->invoke($resourceDetector, 'Apache/2.2.34 (Amazon)'));
        $this->assertEquals('2.4.53', $extractVersionMethod->invoke($resourceDetector, 'Apache/2.4.53 (Debian)'));

        // Test edge cases
        $this->assertNull($extractVersionMethod->invoke($resourceDetector, 'nginx/1.18.0'));
        $this->assertNull($extractVersionMethod->invoke($resourceDetector, 'Invalid version string'));
        $this->assertNull($extractVersionMethod->invoke($resourceDetector, ''));
    }

    protected function tearDown(): void
    {
        // Clean up $_SERVER variables that might affect other tests
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['DOCUMENT_ROOT']);

        parent::tearDown();
    }
}
