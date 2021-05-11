<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Link
{
    public function getBaggage(): Baggage;
    public function getAttributes(): Attributes;
}
