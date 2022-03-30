<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function extension_loaded;
use function getmypid;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use const PHP_BINARY;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/process.md#process
 */
final class Process implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = [];
        $attributes[ResourceAttributes::PROCESS_PID] = getmypid();
        $attributes[ResourceAttributes::PROCESS_EXECUTABLE_PATH] = PHP_BINARY;
        if ($_SERVER['argv'] ?? null) {
            $attributes[ResourceAttributes::PROCESS_COMMAND] = $_SERVER['argv'][0];
            $attributes[ResourceAttributes::PROCESS_COMMAND_ARGS] = $_SERVER['argv'];
        }

        if (extension_loaded('posix')) {
            $attributes[ResourceAttributes::PROCESS_OWNER] = \posix_getpwuid(\posix_geteuid())['name'];
        }

        return ResourceInfo::create(new Attributes($attributes), ResourceAttributes::SCHEMA_URL);
    }
}
