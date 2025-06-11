<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function function_exists;
use function gethostname;
use function hash;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use function php_sapi_name;

/**
 * FPM resource detector that provides stable service instance IDs to avoid high cardinality issues.
 *
 * For FPM environments, generates a stable instance ID based on the pool name and hostname
 * rather than using random UUIDs which cause cardinality explosion in metrics.
 */
final class Fpm implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        // Only activate for FPM SAPI
        if (php_sapi_name() !== 'fpm-fcgi') {
            return ResourceInfoFactory::emptyResource();
        }

        $attributes = [
            ResourceAttributes::SERVICE_INSTANCE_ID => $this->getStableInstanceId(),
        ];

        // Add service name if configured
        $serviceName = Configuration::has(Variables::OTEL_SERVICE_NAME)
            ? Configuration::getString(Variables::OTEL_SERVICE_NAME)
            : null;

        if ($serviceName !== null) {
            $attributes[ResourceAttributes::SERVICE_NAME] = $serviceName;
        }

        // Add FPM-specific attributes
        if (function_exists('fastcgi_finish_request')) {
            $poolName = $this->getFpmPoolName();
            if ($poolName !== null) {
                $attributes['process.runtime.pool'] = $poolName;
            }
        }

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }

    /**
     * Generate a stable service instance ID for FPM processes.
     *
     * Uses pool name + hostname to create a deterministic ID that remains
     * consistent across FPM process restarts within the same pool.
     */
    private function getStableInstanceId(): string
    {
        $components = [
            $this->getFpmPoolName() ?? 'default',
            gethostname() ?: 'localhost',
        ];

        // Create a stable hash-based ID instead of random UUID
        return 'fpm-' . hash('crc32b', implode('-', $components));
    }

    /**
     * Attempt to determine the FPM pool name from environment or server variables.
     */
    private function getFpmPoolName(): ?string
    {
        // Try common FPM pool identification methods
        if (isset($_SERVER['FPM_POOL'])) {
            return $_SERVER['FPM_POOL'];
        }

        if (isset($_ENV['FPM_POOL'])) {
            return $_ENV['FPM_POOL'];
        }

        // Fallback: try to extract from process title if available
        if (function_exists('cli_get_process_title')) {
            $title = cli_get_process_title();
            if ($title && preg_match('/pool\s+(\w+)/', $title, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
