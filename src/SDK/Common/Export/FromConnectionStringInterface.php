<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

interface FromConnectionStringInterface
{
    public static function fromConnectionString(string $endpointUrl, string $name, string $args);
}
