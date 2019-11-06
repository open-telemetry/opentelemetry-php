<?php

declare(strict_types=1);

namespace OpenTelemetry;

interface Transport
{
    public function write(array $data) : bool;
}