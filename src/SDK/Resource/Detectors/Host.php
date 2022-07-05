<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use function php_uname;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/host.md#host
 */
final class Host implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = [
            ResourceAttributes::HOST_NAME => php_uname('n'),
            ResourceAttributes::HOST_ARCH => php_uname('m'),
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
