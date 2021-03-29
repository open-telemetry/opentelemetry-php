<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use Jaeger\Codec\CodecUtility;
use OpenTelemetry\Trace as API;

final class SpanContext implements API\SpanContext
{
    public const INVALID_TRACE = 0;
    public const VALID_TRACE = '/^[0-9a-f]{32}$/';
    public const INVALID_SPAN = 0;
    public const VALID_SPAN = '/^[0-9a-f]{16}$/';
    public const SAMPLED_FLAG = 1;

    /**
     * @var int
     */
    private $traceId;
    /**
     * @var int
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
     * @param int $traceId
     * @param int $spanId
     * @param int $traceFlags
     * @param API\TraceState|null $traceState
     */
    public function __construct(int $traceId, int $spanId, int $traceFlags, ?API\TraceState $traceState = null)
    {
        /**
         * For time being keeping the below validation commented,
         * Later will open it once we have the zipkin exporter working with http traces.
         */

        // if (preg_match(self::VALID_TRACE, $traceId) === 0) {
        //     throw new \InvalidArgumentException(
        //         sprintf('TraceID must be exactly 16 bytes (32 chars) and at least one non-zero byte, got %s', $traceId)
        //     );
        // }
        // if (preg_match(self::VALID_SPAN, $spanId) === 0) {
        //     throw new \InvalidArgumentException(
        //         sprintf('SpanID must be exactly 8 bytes (16 chars) and at least one non-zero byte, got %s', $spanId)
        //     );
        // }

        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->traceState = $traceState;
        $this->isRemote = false;
        $this->isSampled = ($traceFlags & self::SAMPLED_FLAG) === self::SAMPLED_FLAG;
        $this->traceFlags = $traceFlags;
        $this->isValid = $this->traceId !== 0 && $this->spanId !== 0;
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
        return self::fork(CodecUtility::getValidI64(16), $sampled);
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
     * @param int $traceId Existing trace
     * @param bool $sampled Default: false
     * @param bool $isRemote Default: false
     * @return SpanContext
     */
    public static function fork(int $traceId, bool $sampled = false, bool $isRemote = false): SpanContext
    {
        return self::restore($traceId, CodecUtility::getValidI64(8), $sampled, $isRemote);
    }

    /**
     * Generates a context from an already existing trace
     *
     * @param int $traceId
     * @param int $spanId
     * @param bool $sampled
     * @param bool $isRemote Default: false
     * @param API\TraceState|null $traceState
     * @return SpanContext
     */
    public static function restore(int $traceId, int $spanId, bool $sampled = false, bool $isRemote = false, ?API\TraceState $traceState = null): SpanContext
    {
        $sampleFlag = $sampled ? 1 : 0;
        $trace = new self($traceId, $spanId, $sampleFlag, $traceState);
        $trace->isRemote = $isRemote;

        return $trace;
    }

    /**
     * @return int Returns the TraceID
     */
    public function getTraceId(): int
    {
        return $this->traceId;
    }

    /**
     * @return int Returns the SpanID
     */
    public function getSpanId(): int
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

    public function getTraceFlags(): int
    {
        return $this->traceFlags;
    }
}
