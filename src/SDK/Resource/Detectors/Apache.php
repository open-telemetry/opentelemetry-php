<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function apache_get_version;
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
 * Apache resource detector that provides stable service instance IDs to avoid high cardinality issues.
 *
 * For Apache mod_php environments, generates a stable instance ID based on the server name and hostname
 * rather than using random UUIDs which cause cardinality explosion in metrics.
 */
final class Apache implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        // Only activate for Apache SAPI
        if (!$this->isApacheSapi()) {
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

        // Add Apache-specific attributes
        if (function_exists('apache_get_version')) {
            $attributes['webserver.name'] = 'apache';
            $attributes['webserver.version'] = apache_get_version();
        }

        $serverName = $this->getServerName();
        if ($serverName !== null) {
            $attributes['webserver.server_name'] = $serverName;
        }

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }

    /**
     * Generate a stable service instance ID for Apache processes.
     *
     * Uses server name + hostname + document root to create a deterministic ID that remains
     * consistent across Apache process restarts within the same virtual host.
     */
    private function getStableInstanceId(): string
    {
        $components = [
            $this->getServerName() ?? 'default',
            gethostname() ?: 'localhost',
            $this->getDocumentRoot() ?? '/var/www',
        ];

        // Create a stable hash-based ID instead of random UUID
        return 'apache-' . hash('crc32b', implode('-', $components));
    }

    /**
     * Check if running under Apache SAPI.
     */
    private function isApacheSapi(): bool
    {
        $sapi = php_sapi_name();

        return $sapi === 'apache2handler' ||
               $sapi === 'apache' ||
               str_starts_with($sapi, 'apache');
    }

    /**
     * Get the Apache server name from configuration.
     */
    private function getServerName(): ?string
    {
        return $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? null;
    }

    /**
     * Get the document root for this Apache instance.
     */
    private function getDocumentRoot(): ?string
    {
        return $_SERVER['DOCUMENT_ROOT'] ?? null;
    }
}
