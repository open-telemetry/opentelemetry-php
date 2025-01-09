<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\SDK\Resource\ResourceDetectorFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;

class TestResourceDetectorFactory implements ResourceDetectorFactoryInterface
{
<<<<<<< HEAD
=======

>>>>>>> 5ffe83be2c000d079eb1afe1e7381b315dd779f9
    public function create(): ResourceDetectorInterface
    {
        return new TestResourceDetector();
    }

    public function type(): string
    {
        return 'test';
    }

    public function priority(): int
    {
        return 0;
    }
}
