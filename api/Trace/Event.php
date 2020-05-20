<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Event
{
    public function getName(): string;
    public function getAttributes(): Attributes;
    public function getTimestamp(): int;
}
