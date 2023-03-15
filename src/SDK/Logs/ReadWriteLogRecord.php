<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\Context\ContextInterface;

class ReadWriteLogRecord extends ReadableLogRecord
{
    public function setContext(?ContextInterface $context): void
    {
        $this->logRecordData->data['context'] = $context;
    }
}
