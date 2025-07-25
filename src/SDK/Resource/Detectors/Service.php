<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use Ramsey\Uuid\Uuid;

/**
 * @see https://github.com/open-telemetry/semantic-conventions/tree/main/docs/resource#service-experimental
 */
final class Service implements ResourceDetectorInterface
{
    #[\Override]
    public function getResource(): ResourceInfo
    {
        static $serviceInstanceId;
        $serviceInstanceId ??= Uuid::uuid4()->toString();
        $serviceName = Configuration::has(Variables::OTEL_SERVICE_NAME)
            ? Configuration::getString(Variables::OTEL_SERVICE_NAME)
            : null;

        $attributes = [
            ResourceAttributes::SERVICE_INSTANCE_ID => $serviceInstanceId,
        ];
        if ($serviceName !== null) {
            $attributes[ResourceAttributes::SERVICE_NAME] = $serviceName;
        }

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
