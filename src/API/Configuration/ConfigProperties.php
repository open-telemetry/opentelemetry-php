<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration;

interface ConfigProperties
{
    public function get(string $id): mixed;
}
