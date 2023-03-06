<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * @psalm-internal OpenTelemetry
 */
class LogRecordData
{
    public array $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
