<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function file_exists;
use function file_get_contents;
use function getenv;
use function gethostname;
use function is_readable;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use Ramsey\Uuid\Uuid;
use function trim;

/**
 * Kubernetes resource detector that provides stable service instance IDs to avoid high cardinality issues.
 *
 * For Kubernetes environments, generates a stable instance ID based on the pod UID
 * rather than using random UUIDs which cause cardinality explosion in metrics.
 */
final class Kubernetes implements ResourceDetectorInterface
{
    private const K8S_SERVICE_ACCOUNT_PATH = '/var/run/secrets/kubernetes.io/serviceaccount';
    private const K8S_NAMESPACE_FILE = self::K8S_SERVICE_ACCOUNT_PATH . '/namespace';
    private const K8S_TOKEN_FILE = self::K8S_SERVICE_ACCOUNT_PATH . '/token';

    public function getResource(): ResourceInfo
    {
        // Only activate in Kubernetes environments
        if (!$this->isKubernetesEnvironment()) {
            return ResourceInfoFactory::emptyResource();
        }

        $attributes = [];

        // Get pod UID for stable service instance ID
        $podUid = $this->getPodUid();
        if ($podUid !== null) {
            $attributes[ResourceAttributes::SERVICE_INSTANCE_ID] = $this->getStableInstanceId($podUid);
        }

        // Add service name if configured
        $serviceName = Configuration::has(Variables::OTEL_SERVICE_NAME)
            ? Configuration::getString(Variables::OTEL_SERVICE_NAME)
            : null;

        if ($serviceName !== null) {
            $attributes[ResourceAttributes::SERVICE_NAME] = $serviceName;
        }

        // Add Kubernetes-specific attributes
        $this->addKubernetesAttributes($attributes);

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }

    /**
     * Generate a stable service instance ID for Kubernetes pods.
     *
     * Uses pod UID directly as it's already a UUID that remains
     * consistent for the lifetime of the pod.
     */
    private function getStableInstanceId(string $podUid): string
    {
        // Pod UID is already a UUID, but we'll use our standard UUID v5 pattern for consistency
        $components = [
            'k8s',
            $podUid,
            $this->getPodName() ?? 'unknown-pod',
            $this->getNamespace() ?? 'default',
        ];

        // Create a stable UUID v5 using a namespace UUID and deterministic name
        $namespace = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8'); // DNS namespace UUID
        $name = implode('-', $components);

        return Uuid::uuid5($namespace, $name)->toString();
    }

    /**
     * Get environment variable value, checking both $_ENV and getenv().
     */
    private function getEnv(string $name): string|false
    {
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }

        return getenv($name);
    }

    /**
     * Check if running in a Kubernetes environment.
     */
    private function isKubernetesEnvironment(): bool
    {
        // Check for Kubernetes environment variables
        if ($this->getEnv('KUBERNETES_SERVICE_HOST') !== false) {
            return true;
        }

        // Check for service account token file
        if (file_exists(self::K8S_TOKEN_FILE) && is_readable(self::K8S_TOKEN_FILE)) {
            return true;
        }

        // Check for downward API environment variables
        if ($this->getEnv('K8S_POD_NAME') !== false || $this->getEnv('K8S_POD_UID') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get the pod UID from environment variables.
     */
    private function getPodUid(): ?string
    {
        // Try Downward API environment variable first
        $podUid = $this->getEnv('K8S_POD_UID');
        if ($podUid !== false) {
            return $podUid;
        }

        // Alternative environment variable names
        $podUid = $this->getEnv('POD_UID');
        if ($podUid !== false) {
            return $podUid;
        }

        return null;
    }

    /**
     * Get the pod name from environment variables.
     */
    private function getPodName(): ?string
    {
        // Try Downward API environment variable first
        $podName = $this->getEnv('K8S_POD_NAME');
        if ($podName !== false) {
            return $podName;
        }

        // Alternative environment variable names
        $podName = $this->getEnv('POD_NAME');
        if ($podName !== false) {
            return $podName;
        }

        // Fallback to hostname which is usually the pod name in K8s
        $hostname = gethostname();
        if ($hostname !== false) {
            return $hostname;
        }

        return null;
    }

    /**
     * Get the namespace from service account or environment variables.
     */
    private function getNamespace(): ?string
    {
        // Try Downward API environment variable first
        $namespace = $this->getEnv('K8S_NAMESPACE');
        if ($namespace !== false) {
            return $namespace;
        }

        // Alternative environment variable names
        $namespace = $this->getEnv('POD_NAMESPACE');
        if ($namespace !== false) {
            return $namespace;
        }

        // Try reading from service account
        if (file_exists(self::K8S_NAMESPACE_FILE) && is_readable(self::K8S_NAMESPACE_FILE)) {
            $namespace = file_get_contents(self::K8S_NAMESPACE_FILE);
            if ($namespace !== false) {
                return trim($namespace);
            }
        }

        return null;
    }

    /**
     * Get the cluster name from environment variables.
     */
    private function getClusterName(): ?string
    {
        $clusterName = $this->getEnv('K8S_CLUSTER_NAME');
        if ($clusterName !== false) {
            return $clusterName;
        }

        $clusterName = $this->getEnv('CLUSTER_NAME');
        if ($clusterName !== false) {
            return $clusterName;
        }

        return null;
    }

    /**
     * Get the node name from environment variables.
     */
    private function getNodeName(): ?string
    {
        $nodeName = $this->getEnv('K8S_NODE_NAME');
        if ($nodeName !== false) {
            return $nodeName;
        }

        $nodeName = $this->getEnv('NODE_NAME');
        if ($nodeName !== false) {
            return $nodeName;
        }

        return null;
    }

    /**
     * Add Kubernetes-specific resource attributes.
     */
    private function addKubernetesAttributes(array &$attributes): void
    {
        // Add pod attributes
        $this->addPodAttributes($attributes);

        // Add container attributes
        $this->addContainerAttributes($attributes);

        // Add namespace attributes
        $this->addNamespaceAttributes($attributes);

        // Add node attributes
        $this->addNodeAttributes($attributes);

        // Add cluster attributes
        $this->addClusterAttributes($attributes);

        // Add workload resource attributes (deployment, replicaset, etc.)
        $this->addWorkloadAttributes($attributes);
    }

    /**
     * Add pod-specific attributes.
     */
    private function addPodAttributes(array &$attributes): void
    {
        $podName = $this->getPodName();
        if ($podName !== null) {
            $attributes['k8s.pod.name'] = $podName;
        }

        $podUid = $this->getPodUid();
        if ($podUid !== null) {
            $attributes['k8s.pod.uid'] = $podUid;
        }

        // Add pod labels and annotations
        $this->addLabelsAndAnnotations($attributes, 'pod');
    }

    /**
     * Add container-specific attributes.
     */
    private function addContainerAttributes(array &$attributes): void
    {
        $containerName = $this->getEnv('K8S_CONTAINER_NAME');
        if ($containerName !== false) {
            $attributes['k8s.container.name'] = $containerName;
        }

        // Container restart count
        $restartCount = $this->getEnv('K8S_CONTAINER_RESTART_COUNT');
        if ($restartCount !== false && is_numeric($restartCount)) {
            $attributes['k8s.container.restart_count'] = (int) $restartCount;
        }

        // Last terminated reason
        $lastTerminatedReason = $this->getEnv('K8S_CONTAINER_STATUS_LAST_TERMINATED_REASON');
        if ($lastTerminatedReason !== false) {
            $attributes['k8s.container.status.last_terminated_reason'] = $lastTerminatedReason;
        }
    }

    /**
     * Add namespace-specific attributes.
     */
    private function addNamespaceAttributes(array &$attributes): void
    {
        $namespace = $this->getNamespace();
        if ($namespace !== null) {
            $attributes['k8s.namespace.name'] = $namespace;
        }

        // Add namespace labels and annotations
        $this->addLabelsAndAnnotations($attributes, 'namespace');
    }

    /**
     * Add node-specific attributes.
     */
    private function addNodeAttributes(array &$attributes): void
    {
        $nodeName = $this->getNodeName();
        if ($nodeName !== null) {
            $attributes['k8s.node.name'] = $nodeName;
        }

        $nodeUid = $this->getEnv('K8S_NODE_UID');
        if ($nodeUid !== false) {
            $attributes['k8s.node.uid'] = $nodeUid;
        }

        // Add node labels and annotations
        $this->addLabelsAndAnnotations($attributes, 'node');
    }

    /**
     * Add cluster-specific attributes.
     */
    private function addClusterAttributes(array &$attributes): void
    {
        $clusterName = $this->getClusterName();
        if ($clusterName !== null) {
            $attributes['k8s.cluster.name'] = $clusterName;
        }

        $clusterUid = $this->getEnv('K8S_CLUSTER_UID');
        if ($clusterUid !== false) {
            $attributes['k8s.cluster.uid'] = $clusterUid;
        }
    }

    /**
     * Add workload resource attributes (deployment, replicaset, etc.).
     */
    private function addWorkloadAttributes(array &$attributes): void
    {
        $workloadTypes = [
            'deployment',
            'replicaset',
            'statefulset',
            'daemonset',
            'job',
            'cronjob',
            'replicationcontroller',
        ];

        foreach ($workloadTypes as $type) {
            $this->addWorkloadTypeAttributes($attributes, $type);
        }
    }

    /**
     * Add attributes for a specific workload type.
     */
    private function addWorkloadTypeAttributes(array &$attributes, string $type): void
    {
        $nameKey = strtoupper("K8S_{$type}_NAME");
        $uidKey = strtoupper("K8S_{$type}_UID");

        $name = $this->getEnv($nameKey);
        if ($name !== false) {
            $attributes["k8s.{$type}.name"] = $name;
        }

        $uid = $this->getEnv($uidKey);
        if ($uid !== false) {
            $attributes["k8s.{$type}.uid"] = $uid;
        }

        // Add labels and annotations for this workload type
        $this->addLabelsAndAnnotations($attributes, $type);
    }

    /**
     * Add labels and annotations for a given resource type.
     */
    private function addLabelsAndAnnotations(array &$attributes, string $resourceType): void
    {
        // Add labels
        $this->addResourceMetadata($attributes, $resourceType, 'label');

        // Add annotations
        $this->addResourceMetadata($attributes, $resourceType, 'annotation');
    }

    /**
     * Add metadata (labels or annotations) for a specific resource type.
     */
    private function addResourceMetadata(array &$attributes, string $resourceType, string $metadataType): void
    {
        $prefix = strtoupper("K8S_{$resourceType}_{$metadataType}_");

        // Check for environment variables with the pattern K8S_<RESOURCE>_<METADATA>_<KEY>
        foreach ($_ENV as $envKey => $envValue) {
            if (str_starts_with($envKey, $prefix)) {
                $metadataKey = substr($envKey, strlen($prefix));
                // Convert from env var format to attribute format
                // K8S_POD_LABEL_APP_KUBERNETES_IO_NAME -> app.kubernetes.io/name
                $metadataKey = strtolower($metadataKey);
                $metadataKey = str_replace('_kubernetes_io_', '.kubernetes.io/', $metadataKey);
                $metadataKey = str_replace('_', '.', $metadataKey);
                $attributeKey = "k8s.{$resourceType}.{$metadataType}.{$metadataKey}";
                $attributes[$attributeKey] = $envValue;
            }
        }

        // Also check getenv() for cases where $_ENV might not be populated
        // This is more limited as we can't enumerate all environment variables
        $commonLabels = [
            'app', 'app.kubernetes.io/name', 'app.kubernetes.io/instance',
            'app.kubernetes.io/version', 'app.kubernetes.io/component',
            'app.kubernetes.io/part-of', 'app.kubernetes.io/managed-by',
            'version', 'environment', 'tier', 'release',
        ];

        foreach ($commonLabels as $label) {
            $envKey = $prefix . str_replace(['.', '-'], '_', strtoupper($label));
            $value = $this->getEnv($envKey);
            if ($value !== false) {
                $attributeKey = "k8s.{$resourceType}.{$metadataType}.{$label}";
                $attributes[$attributeKey] = $value;
            }
        }
    }
}
