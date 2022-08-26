<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

final class SpanContext implements SpanContextInterface
{
    public const INVALID_TRACE = '00000000000000000000000000000000';
    public const VALID_TRACE = '/^[0-9a-f]{32}$/';
    public const TRACE_LENGTH = 32;
    public const INVALID_SPAN = '0000000000000000';
    public const VALID_SPAN = '/^[0-9a-f]{16}$/';
    public const SPAN_LENGTH_BYTES = 8;
    public const SAMPLED_FLAG = 1;

    private static ?SpanContextInterface $invalidContext = null;

    /**
     * @see https://www.w3.org/TR/trace-context/#trace-flags
     * @see https://www.w3.org/TR/trace-context/#sampled-flag
     */
    private bool $isSampled;

    private string $traceId;
    private string $spanId;
    private ?TraceStateInterface $traceState;
    private bool $isValid;
    private bool $isRemote;
    private int $traceFlags;

    private function __construct(
        string $traceId,
        string $spanId,
        int $traceFlags,
        bool $isRemote,
        TraceStateInterface $traceState = null
    ) {
        // TraceId must be exactly 16 bytes (32 chars) and at least one non-zero byte
        // SpanId must be exactly 8 bytes (16 chars) and at least one non-zero byte
        if (!SpanContextValidator::isValidTraceId($traceId) || !SpanContextValidator::isValidSpanId($spanId)) {
            $traceId = self::INVALID_TRACE;
            $spanId = self::INVALID_SPAN;
        }

        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->traceState = $traceState;
        $this->isRemote = $isRemote;
        $this->isSampled = ($traceFlags & self::SAMPLED_FLAG) === self::SAMPLED_FLAG;
        $this->traceFlags = $traceFlags;
        $this->isValid = SpanContextValidator::isValidTraceId($this->traceId) && SpanContextValidator::isValidSpanId($this->spanId);
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getSpanId(): string
    {
        return $this->spanId;
    }

    public function getTraceState(): ?TraceStateInterface
    {
        return $this->traceState;
    }

    public function isSampled(): bool
    {
        return $this->isSampled;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function isRemote(): bool
    {
        return $this->isRemote;
    }

    public function getTraceFlags(): int
    {
        return $this->traceFlags;
    }

    /** @inheritDoc */
    public static function create(string $traceId, string $spanId, int $traceFlags = self::TRACE_FLAG_DEFAULT, ?TraceStateInterface $traceState = null, bool $isRemote=false): SpanContextInterface
    {
        return new SpanContext(
            $traceId,
            $spanId,
            $traceFlags,
            $isRemote,
            $traceState,
        );
    }

    /** @inheritDoc */
    public static function getInvalid(): SpanContextInterface
    {
        if (null === self::$invalidContext) {
            self::$invalidContext = self::create(self::INVALID_TRACE, self::INVALID_SPAN, 0);
        }

        return self::$invalidContext;
    }
}
