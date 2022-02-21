<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class Constant implements ResourceDetectorInterface
{
    private ResourceInfo $resourceInfo;

    public function __construct(ResourceInfo $resourceInfo)
    {
        $this->resourceInfo = $resourceInfo;
    }

    public function getResource(): ResourceInfo
    {
        return $this->resourceInfo;
    }
}
