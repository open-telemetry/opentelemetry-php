<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

interface EventInterface
{
    public function getName(): string;
    public function getAttributes(): AttributesInterface;
    public function getEpochNanos(): int;
    public function getTotalAttributeCount(): int;
}
