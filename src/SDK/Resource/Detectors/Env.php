<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function explode;
use function getenv;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use function strpos;
use function trim;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/resource/sdk.md#specifying-resource-information-via-an-environment-variable
 */
final class Env implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = [];

        $string = getenv('OTEL_RESOURCE_ATTRIBUTES');
        if ($string && false !== strpos($string, '=')) {
            foreach (explode(',', $string) as $pair) {
                [$key, $value] = explode('=', $pair);
                $attributes[trim($key)] = trim($value);
            }
        }
        //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration
        $serviceName = getenv('OTEL_SERVICE_NAME');
        if ($serviceName) {
            $attributes[ResourceAttributes::SERVICE_NAME] = $serviceName;
        }

        return ResourceInfo::create(new Attributes($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
