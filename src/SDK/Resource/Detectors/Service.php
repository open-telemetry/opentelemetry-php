<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/resource/sdk.md#specifying-resource-information-via-an-environment-variable
 */
final class Service implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        static $serviceInstanceId = null;

        if ($serviceInstanceId === null) {
            $serviceInstanceId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        }

        $attributes = [
            ResourceAttributes::SERVICE_INSTANCE_ID => $serviceInstanceId,
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
