<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing;

class SpanContext
{
    private $traceId;
    private $spanId;

    // todo: add 8 bit TraceFlags; currently has one flag "sampled"
    // todo: add Tracestate: https://www.w3.org/TR/trace-context/#tracestate-header

    public static function generate()
    {
        return self::fork(bin2hex(random_bytes(16)));
    }

    // This method is probably useful, but it's name is confusing
    public static function fork(string $traceId)
    {
        return self::restore($traceId, bin2hex(random_bytes(8)));
    }

    // this is exactly the same as a constructor -- why is it here?
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

    /* TODO : Finish this function */
    public function IsValid() : bool
    {
        return false;
    }

    /* TODO : Finish this function */
    public function IsRemote() : bool
    {
       return false;
    }
}