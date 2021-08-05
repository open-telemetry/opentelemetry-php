<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;
use Throwable;

final class SpanContext implements API\SpanContext
{
    public const INVALID_TRACE = '00000000000000000000000000000000';
    public const VALID_TRACE = '/^[0-9a-f]{32}$/';
    public const TRACE_LENGTH = 32;
    public const TRACE_LENGTH_BYTES = 16;
    public const INVALID_SPAN = '0000000000000000';
    public const VALID_SPAN = '/^[0-9a-f]{16}$/';
    public const SPAN_LENGTH = 16;
    public const SPAN_LENGTH_BYTES = 8;
    public const SAMPLED_FLAG = 1;
    public const TRACE_FLAG_LENGTH = 2;

    /**
     * @var string
     */
    private $traceId;
    /**
     * @var string
     */
    private $spanId;
    /**
     * @var API\TraceState|null
     */
    private $traceState;
    /**
     * @var bool
     * @see https://www.w3.org/TR/trace-context/#trace-flags
     * @see https://www.w3.org/TR/trace-context/#sampled-flag
     */
    private $isSampled;
    /**
     * @var bool
     */
    private $isValid;
    /**
     * @var bool Flag which tells if the context was generated from an already existing trace
     */
    private $isRemote;
    /**
     * @var int
     */
    private $traceFlags;

    /**
     * @param string $traceId
     * @param string $spanId
     * @param int $traceFlags
     * @param API\TraceState|null $traceState
     */
    public function __construct(string $traceId, string $spanId, int $traceFlags, ?API\TraceState $traceState = null)
    {
        // TraceId must be exactly 16 bytes (32 chars) and at least one non-zero byte
        // SpanId must be exactly 8 bytes (16 chars) and at least one non-zero byte
        if (!self::isValidTraceId($traceId) || !self::isValidSpanId($spanId)) {
            $traceId = self::INVALID_TRACE;
            $spanId = self::INVALID_SPAN;
        }

        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->traceState = $traceState;
        $this->isRemote = false;
        $this->isSampled = ($traceFlags & self::SAMPLED_FLAG) === self::SAMPLED_FLAG;
        $this->traceFlags = $traceFlags;
        $this->isValid = self::isValidTraceId($this->traceId) && self::isValidSpanId($this->spanId);
    }

    public static function getInvalid(): API\SpanContext
    {
        return new self(self::INVALID_TRACE, self::INVALID_SPAN, 0);
    }

    /**
     * Creates a new context with random trace
     *
     * @param bool $sampled Default: false
     * @return SpanContext
     */
    public static function generate(bool $sampled = false): SpanContext
    {
        return self::fork(self::randomHex(self::TRACE_LENGTH_BYTES), $sampled);
    }

    /**
     * Creates a new sampled context with random trace
     *
     * @return SpanContext
     */
    public static function generateSampled(): SpanContext
    {
        return self::generate(true);
    }

    /**
     * Creates a new context with random span on the same trace
     *
     * @param string $traceId Existing trace
     * @param bool $sampled Default: false
     * @param bool $isRemote Default: false
     * @return SpanContext
     */
    public static function fork(string $traceId, bool $sampled = false, bool $isRemote = false): SpanContext
    {
        return self::restore($traceId, self::randomHex(self::SPAN_LENGTH_BYTES), $sampled, $isRemote);
    }

    /**
     * Generates a context from an already existing trace
     *
     * @param string $traceId
     * @param string $spanId
     * @param bool $sampled
     * @param bool $isRemote Default: false
     * @param API\TraceState|null $traceState
     * @return SpanContext
     */
    public static function restore(string $traceId, string $spanId, bool $sampled = false, bool $isRemote = false, ?API\TraceState $traceState = null): SpanContext
    {
        $sampleFlag = $sampled ? 1 : 0;
        $trace = new self($traceId, $spanId, $sampleFlag, $traceState);
        $trace->isRemote = $isRemote;

        return $trace;
    }

    /**
     * @return string Returns the TraceID
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }

    /**
     * @return string Returns the SpanID
     */
    public function getSpanId(): string
    {
        return $this->spanId;
    }

    /**
     * @return API\TraceState Returns a Tracestate object containing parsed list-members
     */
    public function getTraceState(): ?API\TraceState
    {
        return $this->traceState;
    }

    /**
     * @return bool Returns a value that indicates if the context needs to be exported
     */
    public function isSampled(): bool
    {
        return $this->isSampled;
    }

    /**
     * @return bool Returns a value that indicates if the context has non-zero trace and span
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return bool Returns a value that indicates if the context was created from a previously existing trace
     */
    public function isRemote(): bool
    {
        return $this->isRemote;
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
     * Generates a random hex string
     *
     * In case where there is not enough entropy for random_bytes() the generation will use a simpler method.
     *
     * @param int $length of bytes
     * @return string
     */
    private static function randomHex(int $length): string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (Throwable $ex) {
            return substr(str_shuffle(str_repeat('0123456789abcdef', $length)), 1, $length);
        }
    }

    public function getTraceFlags(): int
    {
        return $this->traceFlags;
    }
}
