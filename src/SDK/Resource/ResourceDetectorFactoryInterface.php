<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use OpenTelemetry\SDK\Common\Services\SpiLoadableInterface;

interface ResourceDetectorFactoryInterface extends SpiLoadableInterface
{
    public function create(): ResourceDetectorInterface;
}
