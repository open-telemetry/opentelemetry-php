<?php

namespace OpenTelemetry\Example;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;

class TestResourceDetector implements ResourceDetectorInterface
{
    public function getResource(): ResourceInfo
    {
        $attributes = [
            'test-resource' => 'test-value',
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }
}