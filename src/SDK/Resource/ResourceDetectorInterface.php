<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

interface ResourceDetectorInterface
{
    public function getResource(): ResourceInfo;
}
