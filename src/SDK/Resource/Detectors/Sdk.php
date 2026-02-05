<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function class_exists;
use Composer\InstalledVersions;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Attributes\TelemetryAttributes;
use OpenTelemetry\SemConv\Incubating\Attributes\TelemetryIncubatingAttributes;
use OpenTelemetry\SemConv\Version;

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

    #[\Override]
    public function getResource(): ResourceInfo
    {
        $attributes = [
            TelemetryAttributes::TELEMETRY_SDK_NAME => 'opentelemetry',
            TelemetryAttributes::TELEMETRY_SDK_LANGUAGE => 'php',
        ];

        if (class_exists(InstalledVersions::class)) {
            foreach (self::PACKAGES as $package) {
                if (!InstalledVersions::isInstalled($package)) {
                    continue;
                }
                if (($version = InstalledVersions::getPrettyVersion($package)) === null) {
                    continue;
                }

                $attributes[TelemetryAttributes::TELEMETRY_SDK_VERSION] = $version;

                break;
            }
        }

        if (extension_loaded('opentelemetry')) {
            $attributes[TelemetryIncubatingAttributes::TELEMETRY_DISTRO_NAME] = 'opentelemetry-php-instrumentation';
            $attributes[TelemetryIncubatingAttributes::TELEMETRY_DISTRO_VERSION] = phpversion('opentelemetry');
        }

        return ResourceInfo::create(Attributes::create($attributes), Version::VERSION_1_38_0->url());
    }
}
