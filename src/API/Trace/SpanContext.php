<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use OpenTelemetry\API\Trace as API;
use function strlen;
use function strtolower;

final class SpanContext implements API\SpanContextInterface
{
    public const INVALID_TRACE = '00000000000000000000000000000000';
    public const VALID_TRACE = '/^[0-9a-f]{32}$/';
    public const TRACE_LENGTH = 32;
    public const INVALID_SPAN = '0000000000000000';
    public const VALID_SPAN = '/^[0-9a-f]{16}$/';
    public const SPAN_LENGTH = 16;
    public const SPAN_LENGTH_BYTES = 8;
    public const SAMPLED_FLAG = 1;
    public const TRACE_FLAG_LENGTH = 2;

    private static ?API\SpanContextInterface $invalidContext = null;

    /** @inheritDoc */
    public static function createFromRemoteParent(string $traceId, string $spanId, int $traceFlags = self::TRACE_FLAG_DEFAULT, ?API\TraceStateInterface $traceState = null): API\SpanContextInterface
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
    public static function create(string $traceId, string $spanId, int $traceFlags = self::TRACE_FLAG_DEFAULT, ?API\TraceStateInterface $traceState = null): API\SpanContextInterface
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
    public static function getInvalid(): API\SpanContextInterface
    {
        if (null === self::$invalidContext) {
            self::$invalidContext = self::create(self::INVALID_TRACE, self::INVALID_SPAN, 0);
        }

        return self::$invalidContext;
    }

    /**
     * @return bool Returns a value that indicates whether a trace id is valid
     */
    public static function isValidTraceId($traceId): bool
    {
        return ctype_xdigit($traceId) && strlen($traceId) === self::TRACE_LENGTH && $traceId !== self::INVALID_TRACE && $traceId === strtolower($traceId);
    }

    /**
     * @return bool Returns a value that indicates whether a span id is valid
     */
    public static function isValidSpanId($spanId): bool
    {
        return ctype_xdigit($spanId) && strlen($spanId) === self::SPAN_LENGTH && $spanId !== self::INVALID_SPAN && $spanId === strtolower($spanId);
    }

    /**
     * @return bool Returns a value that indicates whether trace flag is valid
     * TraceFlags must be exactly 1 bytes (1 char) representing a bit field
     */
    public static function isValidTraceFlag($traceFlag): bool
    {
        return ctype_xdigit($traceFlag) && strlen($traceFlag) === self::TRACE_FLAG_LENGTH;
    }

    /**
     * @see https://www.w3.org/TR/trace-context/#trace-flags
     * @see https://www.w3.org/TR/trace-context/#sampled-flag
     */
    private bool $isSampled;

    private string $traceId;
    private string $spanId;
    private ?API\TraceStateInterface $traceState;
    private bool $isValid;
    private bool $isRemote;
    private int $traceFlags;

    private function __construct(
        string $traceId,
        string $spanId,
        int $traceFlags,
        bool $isRemote,
        API\TraceStateInterface $traceState = null
    ) {
        // TraceId must be exactly 16 bytes (32 chars) and at least one non-zero byte
        // SpanId must be exactly 8 bytes (16 chars) and at least one non-zero byte
        if (!self::isValidTraceId($traceId) || !self::isValidSpanId($spanId)) {
            $traceId = self::INVALID_TRACE;
            $spanId = self::INVALID_SPAN;
        }

        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->traceState = $traceState;
        $this->isRemote = $isRemote;
        $this->isSampled = ($traceFlags & self::SAMPLED_FLAG) === self::SAMPLED_FLAG;
        $this->traceFlags = $traceFlags;
        $this->isValid = self::isValidTraceId($this->traceId) && self::isValidSpanId($this->spanId);
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getSpanId(): string
    {
        return $this->spanId;
    }

    public function getTraceState(): ?API\TraceStateInterface
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
}
