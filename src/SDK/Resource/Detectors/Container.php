<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.18.0/specification/resource/semantic_conventions/container.md
 */
final class Container implements ResourceDetectorInterface
{
    private string $dir;
    private const CONTAINER_ID_LENGTH = 64;
    private const CGROUP_V1 = 'cgroup';
    private const CGROUP_V2 = 'mountinfo';
    private const HOSTNAME = 'hostname';

    public function __construct(string $dir = '/proc/self')
    {
        $this->dir = $dir;
    }

    public function getResource(): ResourceInfo
    {
        $attributes = [];
        $id = $this->getContainerId();
        if ($id) {
            $attributes[ResourceAttributes::CONTAINER_ID] = $id;
        }

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }

    private function getContainerId(): ?string
    {
        return $this->getContainerIdV2() ?? $this->getContainerIdV1();
    }

    private function getContainerIdV1(): ?string
    {
        if (!file_exists(sprintf('%s/%s', $this->dir, self::CGROUP_V1))) {
            return null;
        }
        $data = file_get_contents(sprintf('%s/%s', $this->dir, self::CGROUP_V1));
        if (!$data) {
            return null;
        }
        $lines = explode('\n', $data);
        foreach ($lines as $line) {
            if (strlen($line) >= self::CONTAINER_ID_LENGTH) {
                //if string is longer than CONTAINER_ID_LENGTH, return the last CONTAINER_ID_LENGTH chars
                return substr($line, strlen($line) - self::CONTAINER_ID_LENGTH);
            }
        }

        return null;
    }
    private function getContainerIdV2(): ?string
    {
        if (!file_exists(sprintf('%s/%s', $this->dir, self::CGROUP_V1))) {
            return null;
        }
        $data = file_get_contents(sprintf('%s/%s', $this->dir, self::CGROUP_V2));
        if (!$data) {
            return null;
        }
        $lines = explode(PHP_EOL, $data);
        foreach ($lines as $line) {
            if (strpos($line, self::HOSTNAME) !== false) {
                $parts = explode('/', $line);
                foreach ($parts as $part) {
                    if (strlen($part) === self::CONTAINER_ID_LENGTH) {
                        return $part;
                    }
                }
            }
        }

        return null;
    }
}
