<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final readonly class Constant implements ResourceDetectorInterface
{
    public function __construct(private ResourceInfo $resourceInfo)
    {
    }

    #[\Override]
    public function getResource(): ResourceInfo
    {
        return $this->resourceInfo;
    }
}
