<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/resource/sdk.md#specifying-resource-information-via-an-environment-variable
 */
final class Environment implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = EnvironmentVariables::getMap(Env::OTEL_RESOURCE_ATTRIBUTES, '');

        //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration
        $serviceName = EnvironmentVariables::has(Env::OTEL_SERVICE_NAME) ?
                       EnvironmentVariables::getString(Env::OTEL_SERVICE_NAME) :
                       null;
        if ($serviceName) {
            $attributes[ResourceAttributes::SERVICE_NAME] = $serviceName;
        }

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
