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
    private readonly bool $isSampled;
    private bool $isValid = true;

    private function __construct(
        private string $traceId,
        private string $spanId,
        private readonly int $traceFlags,
        private readonly bool $isRemote,
        private readonly ?TraceStateInterface $traceState = null,
    ) {
        // TraceId must be exactly 16 bytes (32 chars) and at least one non-zero byte
        // SpanId must be exactly 8 bytes (16 chars) and at least one non-zero byte
        if (!SpanContextValidator::isValidTraceId($traceId) || !SpanContextValidator::isValidSpanId($spanId)) {
            $this->traceId = SpanContextValidator::INVALID_TRACE;
            $this->spanId = SpanContextValidator::INVALID_SPAN;
            $this->isValid=false;
        }

        $this->isSampled = ($traceFlags & TraceFlags::SAMPLED) === TraceFlags::SAMPLED;
    }

    #[\Override]
    public function getTraceId(): string
    {
        return $this->traceId;
    }

    #[\Override]
    public function getTraceIdBinary(): string
    {
        return hex2bin($this->traceId);
    }

    #[\Override]
    public function getSpanId(): string
    {
        return $this->spanId;
    }

    #[\Override]
    public function getSpanIdBinary(): string
    {
        return hex2bin($this->spanId);
    }

    #[\Override]
    public function getTraceState(): ?TraceStateInterface
    {
        return $this->traceState;
    }

    #[\Override]
    public function isSampled(): bool
    {
        return $this->isSampled;
    }

    #[\Override]
    public function isValid(): bool
    {
        return $this->isValid;
    }

    #[\Override]
    public function isRemote(): bool
    {
        return $this->isRemote;
    }

    #[\Override]
    public function getTraceFlags(): int
    {
        return $this->traceFlags;
    }

    /** @inheritDoc */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public static function getInvalid(): SpanContextInterface
    {
        if (null === self::$invalidContext) {
            self::$invalidContext = self::create(SpanContextValidator::INVALID_TRACE, SpanContextValidator::INVALID_SPAN, 0);
        }

        return self::$invalidContext;
    }
}
