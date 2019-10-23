<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing;

class SpanContext
{
    private $traceId;
    private $spanId;

    public static function generate()
    {
        return self::fork(bin2hex(random_bytes(16)));
    }

    public static function fork(string $traceId)
    {
        return self::restore($traceId, bin2hex(random_bytes(8)));
    }

    public static function restore(string $traceId, string $spanId)
    {
        return new self($traceId, $spanId);
    }

    public function __construct(string $traceId, string $spanId)
    {
        $this->traceId = $traceId;
        $this->spanId = $spanId;
    }

    public function getTraceId() : string
    {
        return $this->traceId;
    }

    public function getSpanId() : string
    {
        return $this->spanId;
    }
}