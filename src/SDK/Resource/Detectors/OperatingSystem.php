<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * @deprecated Use Host detector instead.
 */
final class OperatingSystem implements ResourceDetectorInterface
{
    #[\Override]
    public function getResource(): ResourceInfo
    {
        return ResourceInfo::emptyResource();
    }
}
