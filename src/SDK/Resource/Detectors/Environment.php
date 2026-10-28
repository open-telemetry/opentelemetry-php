<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Version;

/**
 * A malformed value is discarded in full, not pair by pair.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/resource/sdk.md#specifying-resource-information-via-an-environment-variable
 */
final class Environment implements ResourceDetectorInterface
{
    use LogsMessagesTrait;

    #[\Override]
    public function getResource(): ResourceInfo
    {
        $attributes = [];
        if (Configuration::has(Variables::OTEL_RESOURCE_ATTRIBUTES)) {
            try {
                $attributes = self::decode(Configuration::getMap(Variables::OTEL_RESOURCE_ATTRIBUTES, []));
            } catch (InvalidArgumentException $e) {
                self::logWarning(sprintf('Ignoring %s: %s', Variables::OTEL_RESOURCE_ATTRIBUTES, $e->getMessage()));
            }
        }

        return ResourceInfo::create(Attributes::create($attributes), Version::VERSION_1_38_0->url());
    }

    private static function decode(array $attributes): array
    {
        return array_map(urldecode(...), $attributes);
    }
}
