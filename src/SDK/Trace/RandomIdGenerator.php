<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use Throwable;

class RandomIdGenerator implements IdGeneratorInterface
{
    private const TRACE_ID_HEX_LENGTH = 32;
    private const SPAN_ID_HEX_LENGTH = 16;

    public function generateTraceId(): string
    {
        return $this->randomHex(self::TRACE_ID_HEX_LENGTH);
    }

    public function generateSpanId(): string
    {
        return $this->randomHex(self::SPAN_ID_HEX_LENGTH);
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
