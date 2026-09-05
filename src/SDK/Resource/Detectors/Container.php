<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\Attributes\ContainerAttributes;
use OpenTelemetry\SemConv\Version;

/**
 * Detects container resource attributes by reading the container ID from the
 * cgroup filesystem available on Linux.
 *
 * @see https://github.com/open-telemetry/semantic-conventions/blob/main/docs/resource/container.md
 */
final class Container implements ResourceDetectorInterface
{
    private const CGROUP_V1_PATH = '/proc/self/cgroup';
    private const CONTAINER_ID_PATTERN = '/[a-f0-9]{64}/';

    public function __construct(
        private readonly string $cgroupPath = self::CGROUP_V1_PATH,
    ) {
    }

    #[\Override]
    public function getResource(): ResourceInfo
    {
        $containerId = $this->detectContainerId();

        if ($containerId === null) {
            return ResourceInfoFactory::emptyResource();
        }

        return ResourceInfo::create(
            Attributes::create([
                ContainerAttributes::CONTAINER_ID => $containerId,
            ]),
            Version::VERSION_1_43_0->url(),
        );
    }

    private function detectContainerId(): ?string
    {
        // cgroup v1: read /proc/self/cgroup — container ID appears as a 64-char hex string
        if (is_readable($this->cgroupPath)) {
            $content = @file_get_contents($this->cgroupPath);
            if ($content !== false) {
                foreach (explode("\n", $content) as $line) {
                    $parts = explode(':', $line, 3);
                    if (count($parts) === 3) {
                        if (preg_match(self::CONTAINER_ID_PATTERN, $parts[2], $matches) === 1) {
                            return $matches[0];
                        }
                    }
                }
            }
        }

        return null;
    }
}
