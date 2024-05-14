<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use Ramsey\Uuid\Uuid;

/**
 * @see https://github.com/open-telemetry/semantic-conventions/tree/main/docs/resource#service-experimental
 */
final class Service implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        static $serviceInstanceId;
        $serviceInstanceId ??= Uuid::uuid4()->toString();

        $attributes = [
            ResourceAttributes::SERVICE_INSTANCE_ID => $serviceInstanceId,
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
