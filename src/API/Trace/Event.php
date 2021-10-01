<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface Event
{
    public function getName(): string;
    public function getAttributes(): Attributes;
    public function getEpochNanos(): int;
    public function getTotalAttributeCount(): int;
}
