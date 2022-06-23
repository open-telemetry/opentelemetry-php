<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use const PHP_OS;
use const PHP_OS_FAMILY;
use function php_uname;
use function strtolower;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/os.md
 */
final class OperatingSystem implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = [
            ResourceAttributes::OS_TYPE => strtolower(PHP_OS_FAMILY),
            ResourceAttributes::OS_DESCRIPTION => php_uname('r'),
            ResourceAttributes::OS_NAME => PHP_OS,
            ResourceAttributes::OS_VERSION => php_uname('v'),
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
