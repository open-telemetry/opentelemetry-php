<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final readonly class Composite implements ResourceDetectorInterface
{
    /**
     * @param iterable<ResourceDetectorInterface> $resourceDetectors
     */
    public function __construct(private iterable $resourceDetectors)
    {
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
