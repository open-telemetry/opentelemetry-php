<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function class_exists;
use Composer\InstalledVersions;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;

/**
 * The SDK MUST provide access to a Resource with at least the attributes listed
 * at {@see https://github.com/open-telemetry/semantic-conventions/blob/v1.32.0/docs/resource/README.md#semantic-attributes-with-sdk-provided-default-value}
 */
final class Sdk implements ResourceDetectorInterface
{
    private const PACKAGES = [
        'open-telemetry/sdk',
        'open-telemetry/opentelemetry',
    ];

    public function getResource(): ResourceInfo
    {
        $attributes = [
            ResourceAttributes::TELEMETRY_SDK_NAME => 'opentelemetry',
            ResourceAttributes::TELEMETRY_SDK_LANGUAGE => 'php',
        ];

        if (class_exists(InstalledVersions::class)) {
            foreach (self::PACKAGES as $package) {
                if (!InstalledVersions::isInstalled($package)) {
                    continue;
                }
                if (($version = InstalledVersions::getPrettyVersion($package)) === null) {
                    continue;
                }

                $attributes[ResourceAttributes::TELEMETRY_SDK_VERSION] = $version;

                break;
            }
        }

        if (extension_loaded('opentelemetry')) {
            $attributes[ResourceAttributes::TELEMETRY_DISTRO_NAME] = 'opentelemetry-php-instrumentation';
            $attributes[ResourceAttributes::TELEMETRY_DISTRO_VERSION] = phpversion('opentelemetry');
        }

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
