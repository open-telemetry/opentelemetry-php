<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

interface ResourceDetectorFactoryInterface
{
    public function create(): ResourceDetectorInterface;
    public function type(): string;
    public function priority(): int;
}
