<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use Throwable;

final class SpanContext
{
    private const INVALID_TRACE = '00000000000000000000000000000000';
    private const VALID_TRACE = '/^[0-9a-f]{32}$/';
    private const INVALID_SPAN = '0000000000000000';
    private const VALID_SPAN = '/^[0-9a-f]{16}$/';
    private const SAMPLED_FLAG = 1;

    /**
     * @var string
     */
    private $traceId;
    /**
     * @var string
     */
    private $spanId;
    /**
     * @var string[]
     * @see https://www.w3.org/TR/trace-context/#tracestate-header
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
     * @param string $traceId
     * @param string $spanId
     * @param int $traceFlags
     * @param array $traceState
     */
    public function __construct(string $traceId, string $spanId, int $traceFlags, array $traceState = [])
    {
        if (preg_match(self::VALID_TRACE, $traceId) === 0) {
            throw new \InvalidArgumentException(
                sprintf('TraceID must be exactly 16 bytes (32 chars) and at least one non-zero byte, got %s', $traceId)
            );
        }
        if (preg_match(self::VALID_SPAN, $spanId) === 0) {
            throw new \InvalidArgumentException(
                sprintf('SpanID must be exactly 8 bytes (16 chars) and at least one non-zero byte, got %s', $spanId)
            );
        }

        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->traceState = $traceState;
        $this->isRemote = false;
        $this->isSampled = ($traceFlags & self::SAMPLED_FLAG) === self::SAMPLED_FLAG;
        $this->isValid = $this->traceId !== self::INVALID_TRACE && $this->spanId !== self::INVALID_SPAN;
    }

    /**
     * Creates a new context with random trace
     *
     * @return SpanContext
     */
    public static function generate(): SpanContext
    {
        return self::fork(self::randomHex(16));
    }

    /**
     * Creates a new context with random span on the same trace
     *
     * @param string $traceId Existing trace
     * @return SpanContext
     */
    public static function fork(string $traceId): SpanContext
    {
        return self::restore($traceId, self::randomHex(8));
    }

    /**
     * Generates a context from an already existing trace
     *
     * @param string $traceId
     * @param string $spanId
     * @param bool $sampled
     * @return SpanContext
     */
    public static function restore(string $traceId, string $spanId, bool $sampled = false): SpanContext
    {
        $sampleFlag = $sampled ? 1 : 0;
        $trace = new self($traceId, $spanId, $sampleFlag, []);
        $trace->isRemote = true;

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
     * @return string[] Returns a key-value array of extra vendor headers
     */
    public function getTraceState(): array
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
}
