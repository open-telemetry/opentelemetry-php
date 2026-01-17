<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Version;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/resource/sdk.md#specifying-resource-information-via-an-environment-variable
 */
final class Environment implements ResourceDetectorInterface
{
    #[\Override]
    public function getResource(): ResourceInfo
    {
        $attributes = Configuration::has(Variables::OTEL_RESOURCE_ATTRIBUTES)
            ? self::decode(Configuration::getMap(Variables::OTEL_RESOURCE_ATTRIBUTES, []))
            : [];

        return ResourceInfo::create(Attributes::create($attributes), Version::VERSION_1_38_0->url());
    }

    private static function decode(array $attributes): array
    {
        return array_map('urldecode', $attributes);
    }
}
