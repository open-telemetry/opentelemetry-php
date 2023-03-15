<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * @psalm-internal OpenTelemetry
 */
class LogRecordData
{
    //@todo improve how this is stored, ie not in a freestyle array
    public array $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
