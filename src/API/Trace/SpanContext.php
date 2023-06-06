<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use function hex2bin;

final class SpanContext implements SpanContextInterface
{
    private static ?SpanContextInterface $invalidContext = null;

    /**
     * @see https://www.w3.org/TR/trace-context/#trace-flags
     * @see https://www.w3.org/TR/trace-context/#sampled-flag
     */
    private bool $isSampled;

    private string $traceId;
    private string $spanId;
    private ?TraceStateInterface $traceState;
    private bool $isValid = true;
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
            $traceId = SpanContextValidator::INVALID_TRACE;
            $spanId = SpanContextValidator::INVALID_SPAN;
            $this->isValid=false;
        }

        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->traceState = $traceState;
        $this->isRemote = $isRemote;
        $this->isSampled = ($traceFlags & TraceFlags::SAMPLED) === TraceFlags::SAMPLED;
        $this->traceFlags = $traceFlags;
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getTraceIdBinary(): string
    {
        return hex2bin($this->traceId);
    }

    public function getSpanId(): string
    {
        return $this->spanId;
    }

    public function getSpanIdBinary(): string
    {
        return hex2bin($this->spanId);
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
    public static function createFromRemoteParent(string $traceId, string $spanId, int $traceFlags = TraceFlags::DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface
    {
        return new self(
            $traceId,
            $spanId,
            $traceFlags,
            true,
            $traceState,
        );
    }

    /** @inheritDoc */
    public static function create(string $traceId, string $spanId, int $traceFlags = TraceFlags::DEFAULT, ?TraceStateInterface $traceState = null): SpanContextInterface
    {
        return new self(
            $traceId,
            $spanId,
            $traceFlags,
            false,
            $traceState,
        );
    }

    /** @inheritDoc */
    public static function getInvalid(): SpanContextInterface
    {
        if (null === self::$invalidContext) {
            self::$invalidContext = self::create(SpanContextValidator::INVALID_TRACE, SpanContextValidator::INVALID_SPAN, 0);
        }

        return self::$invalidContext;
    }
}
