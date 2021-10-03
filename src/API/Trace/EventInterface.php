<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface EventInterface
{
    public function getName(): string;
    public function getAttributes(): AttributesInterface;
    public function getEpochNanos(): int;
    public function getTotalAttributeCount(): int;
}
