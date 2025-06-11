<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Kubernetes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Kubernetes::class)]
class KubernetesTest extends TestCase
{
    public function test_kubernetes_returns_empty_for_non_k8s_environment(): void
    {
        // Ensure all K8s environment variables are cleared
        $this->clearKubernetesEnvironment();

        // Since we're not running in K8s environment in tests, should return empty resource
        $resourceDetector = new Kubernetes();
        $resource = $resourceDetector->getResource();

        $this->assertCount(0, $resource->getAttributes());
    }

    public function test_kubernetes_generates_stable_instance_id(): void
    {
        $resourceDetector = new Kubernetes();

        // Mock K8s environment by creating a reflection to access private methods
        $reflection = new \ReflectionClass($resourceDetector);
        $getStableInstanceIdMethod = $reflection->getMethod('getStableInstanceId');
        $getStableInstanceIdMethod->setAccessible(true);

        // Call the method twice with same pod UID to ensure it's deterministic
        $podUid = 'test-pod-uid-123-456-789';
        $instanceId1 = $getStableInstanceIdMethod->invoke($resourceDetector, $podUid);
        $instanceId2 = $getStableInstanceIdMethod->invoke($resourceDetector, $podUid);

        $this->assertSame($instanceId1, $instanceId2);
        // Should be a valid UUID format (v5)
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $instanceId1);
    }

    public function test_kubernetes_environment_detection(): void
    {
        // Ensure clean environment
        $this->clearKubernetesEnvironment();

        $resourceDetector = new Kubernetes();
        $reflection = new \ReflectionClass($resourceDetector);
        $isKubernetesEnvironmentMethod = $reflection->getMethod('isKubernetesEnvironment');
        $isKubernetesEnvironmentMethod->setAccessible(true);

        // Test detection logic (will be false in CLI test environment)
        $result = $isKubernetesEnvironmentMethod->invoke($resourceDetector);
        $this->assertFalse($result);
    }

    public function test_pod_uid_detection(): void
    {
        $this->clearKubernetesEnvironment();

        $resourceDetector = new Kubernetes();
        $reflection = new \ReflectionClass($resourceDetector);
        $getPodUidMethod = $reflection->getMethod('getPodUid');
        $getPodUidMethod->setAccessible(true);

        // Test with K8S_POD_UID environment variable
        $_ENV['K8S_POD_UID'] = 'test-pod-uid-123';
        $podUid = $getPodUidMethod->invoke($resourceDetector);
        $this->assertSame('test-pod-uid-123', $podUid);
        unset($_ENV['K8S_POD_UID']);

        // Test with POD_UID fallback
        $_ENV['POD_UID'] = 'fallback-pod-uid';
        $podUid = $getPodUidMethod->invoke($resourceDetector);
        $this->assertSame('fallback-pod-uid', $podUid);
        unset($_ENV['POD_UID']);

        // Test without any environment variable set
        $podUid = $getPodUidMethod->invoke($resourceDetector);
        $this->assertNull($podUid);
    }

    public function test_pod_name_detection(): void
    {
        $this->clearKubernetesEnvironment();

        $resourceDetector = new Kubernetes();
        $reflection = new \ReflectionClass($resourceDetector);
        $getPodNameMethod = $reflection->getMethod('getPodName');
        $getPodNameMethod->setAccessible(true);

        // Test with K8S_POD_NAME environment variable
        $_ENV['K8S_POD_NAME'] = 'test-pod-name';
        $podName = $getPodNameMethod->invoke($resourceDetector);
        $this->assertSame('test-pod-name', $podName);
        unset($_ENV['K8S_POD_NAME']);

        // Test with POD_NAME fallback
        $_ENV['POD_NAME'] = 'fallback-pod';
        $podName = $getPodNameMethod->invoke($resourceDetector);
        $this->assertSame('fallback-pod', $podName);
        unset($_ENV['POD_NAME']);

        // Test without environment variables (should use hostname)
        $podName = $getPodNameMethod->invoke($resourceDetector);
        $this->assertNotNull($podName); // Should return hostname
    }

    public function test_namespace_detection(): void
    {
        $this->clearKubernetesEnvironment();

        $resourceDetector = new Kubernetes();
        $reflection = new \ReflectionClass($resourceDetector);
        $getNamespaceMethod = $reflection->getMethod('getNamespace');
        $getNamespaceMethod->setAccessible(true);

        // Test with K8S_NAMESPACE environment variable
        $_ENV['K8S_NAMESPACE'] = 'test-namespace';
        $namespace = $getNamespaceMethod->invoke($resourceDetector);
        $this->assertSame('test-namespace', $namespace);
        unset($_ENV['K8S_NAMESPACE']);

        // Test with POD_NAMESPACE fallback
        $_ENV['POD_NAMESPACE'] = 'fallback-namespace';
        $namespace = $getNamespaceMethod->invoke($resourceDetector);
        $this->assertSame('fallback-namespace', $namespace);
        unset($_ENV['POD_NAMESPACE']);

        // Test without environment variables
        $namespace = $getNamespaceMethod->invoke($resourceDetector);
        $this->assertNull($namespace);
    }

    public function test_cluster_name_detection(): void
    {
        $this->clearKubernetesEnvironment();

        $resourceDetector = new Kubernetes();
        $reflection = new \ReflectionClass($resourceDetector);
        $getClusterNameMethod = $reflection->getMethod('getClusterName');
        $getClusterNameMethod->setAccessible(true);

        // Test with K8S_CLUSTER_NAME environment variable
        $_ENV['K8S_CLUSTER_NAME'] = 'test-cluster';
        $clusterName = $getClusterNameMethod->invoke($resourceDetector);
        $this->assertSame('test-cluster', $clusterName);
        unset($_ENV['K8S_CLUSTER_NAME']);

        // Test with CLUSTER_NAME fallback
        $_ENV['CLUSTER_NAME'] = 'fallback-cluster';
        $clusterName = $getClusterNameMethod->invoke($resourceDetector);
        $this->assertSame('fallback-cluster', $clusterName);
        unset($_ENV['CLUSTER_NAME']);

        // Test without environment variables
        $clusterName = $getClusterNameMethod->invoke($resourceDetector);
        $this->assertNull($clusterName);
    }

    public function test_node_name_detection(): void
    {
        $this->clearKubernetesEnvironment();

        $resourceDetector = new Kubernetes();
        $reflection = new \ReflectionClass($resourceDetector);
        $getNodeNameMethod = $reflection->getMethod('getNodeName');
        $getNodeNameMethod->setAccessible(true);

        // Test with K8S_NODE_NAME environment variable
        $_ENV['K8S_NODE_NAME'] = 'test-node';
        $nodeName = $getNodeNameMethod->invoke($resourceDetector);
        $this->assertSame('test-node', $nodeName);
        unset($_ENV['K8S_NODE_NAME']);

        // Test with NODE_NAME fallback
        $_ENV['NODE_NAME'] = 'fallback-node';
        $nodeName = $getNodeNameMethod->invoke($resourceDetector);
        $this->assertSame('fallback-node', $nodeName);
        unset($_ENV['NODE_NAME']);

        // Test without environment variables
        $nodeName = $getNodeNameMethod->invoke($resourceDetector);
        $this->assertNull($nodeName);
    }

    private function clearKubernetesEnvironment(): void
    {
        // Clean up environment variables that might affect tests
        $envVars = [
            'KUBERNETES_SERVICE_HOST',
            'K8S_POD_UID',
            'POD_UID',
            'K8S_POD_NAME',
            'POD_NAME',
            'K8S_NAMESPACE',
            'POD_NAMESPACE',
            'K8S_CLUSTER_NAME',
            'CLUSTER_NAME',
            'K8S_NODE_NAME',
            'NODE_NAME',
            'K8S_CONTAINER_NAME',
        ];

        foreach ($envVars as $var) {
            unset($_ENV[$var]);
            putenv($var);
        }
    }

    protected function tearDown(): void
    {
        $this->clearKubernetesEnvironment();
        parent::tearDown();
    }
}
