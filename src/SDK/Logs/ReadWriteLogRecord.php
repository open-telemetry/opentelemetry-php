<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

class ReadWriteLogRecord extends ReadableLogRecord
{
    public function setTraceId(string $traceId): self
    {
        $this->logRecordData->data['trace_id'] = $traceId;
        return $this;
    }

    public function setSpanId(string $spanId): self
    {
        $this->logRecordData->data['span_id'] = $spanId;
        return $this;
    }

    public function setTraceFlags(int $traceFlags): self
    {
        $this->logRecordData->data['trace_flags'] = $traceFlags;
        return $this;
    }
}
