<?php

declare(strict_types=1);

namespace OpenTelemetry;

interface TransportInterface
{
    public function write(array $data) : bool;
}
