<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace\SpanContextValidator;
use Throwable;

class RandomIdGenerator implements IdGeneratorInterface
{
    private const TRACE_ID_HEX_LENGTH = 32;
    private const SPAN_ID_HEX_LENGTH = 16;

    public function generateTraceId(): string
    {
        do {
            $traceId = $this->randomHex(self::TRACE_ID_HEX_LENGTH);
        } while (!SpanContextValidator::isValidTraceId($traceId));

        return $traceId;
    }

    public function generateSpanId(): string
    {
        do {
            $spanId = $this->randomHex(self::SPAN_ID_HEX_LENGTH);
        } while (!SpanContextValidator::isValidSpanId($spanId));

        return $spanId;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion $hexLength is always a positive integer
     */
    private function randomHex(int $hexLength): string
    {
        try {
            return bin2hex(random_bytes(intdiv($hexLength, 2)));
        } catch (Throwable $e) {
            return $this->fallbackAlgorithm($hexLength);
        }
    }

    private function fallbackAlgorithm(int $hexLength): string
    {
        return substr(str_shuffle(str_repeat('0123456789abcdef', $hexLength)), 1, $hexLength);
    }
}
