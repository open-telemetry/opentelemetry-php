<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final class Composite implements ResourceDetectorInterface
{
    private iterable $resourceDetectors;

    /**
     * @param iterable<ResourceDetectorInterface> $resourceDetectors
     */
    public function __construct(iterable $resourceDetectors)
    {
        $this->resourceDetectors = $resourceDetectors;
    }

    public function getResource(): ResourceInfo
    {
        $resource = ResourceInfoFactory::emptyResource();
        foreach ($this->resourceDetectors as $resourceDetector) {
            $resource = $resource->merge($resourceDetector->getResource());
        }

        return $resource;
    }
}
