<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use function php_sapi_name;
use const PHP_VERSION;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/process.md#process-runtimes
 */
final class ProcessRuntime implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = [
            ResourceAttributes::PROCESS_RUNTIME_NAME => php_sapi_name(),
            ResourceAttributes::PROCESS_RUNTIME_VERSION => PHP_VERSION,
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
