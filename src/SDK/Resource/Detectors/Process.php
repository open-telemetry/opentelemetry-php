<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function extension_loaded;
use function getmypid;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Incubating\Attributes\ProcessIncubatingAttributes;
use OpenTelemetry\SemConv\Version;
use const PHP_BINARY;
use function php_sapi_name;
use const PHP_VERSION;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/process.md#process
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/process.md#process-runtimes
 */
final class Process implements ResourceDetectorInterface
{
    /**
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    #[\Override]
    public function getResource(): ResourceInfo
    {
        $attributes = [
            ProcessIncubatingAttributes::PROCESS_RUNTIME_NAME => php_sapi_name(),
            ProcessIncubatingAttributes::PROCESS_RUNTIME_VERSION => PHP_VERSION,
            ProcessIncubatingAttributes::PROCESS_PID => getmypid(),
            ProcessIncubatingAttributes::PROCESS_EXECUTABLE_PATH => PHP_BINARY,
        ];

        /**
         * @psalm-suppress PossiblyUndefinedArrayOffset
         */
        if ($_SERVER['argv'] ?? null) {
            $attributes[ProcessIncubatingAttributes::PROCESS_COMMAND] = $_SERVER['argv'][0];
            $attributes[ProcessIncubatingAttributes::PROCESS_COMMAND_ARGS] = $_SERVER['argv'];
        }

        /** @phan-suppress-next-line PhanTypeComparisonFromArray */
        if (extension_loaded('posix') && ($user = \posix_getpwuid(\posix_geteuid())) !== false) {
            $attributes[ProcessIncubatingAttributes::PROCESS_OWNER] = $user['name'];
        }

        return ResourceInfo::create(Attributes::create($attributes), Version::VERSION_1_38_0->url());
    }
}
