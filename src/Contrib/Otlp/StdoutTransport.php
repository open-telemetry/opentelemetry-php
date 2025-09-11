<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;

/**
 * @internal
 *
 * @template-implements TransportInterface<"stdout/nd-json">
 */
class StdoutTransport implements TransportInterface
{
    private $stream;
    private bool $closed = false;

    public function __construct()
    {
        $this->stream = fopen('php://stdout', 'w');
        if ($this->stream === false) {
            throw new \RuntimeException('Failed to open stdout stream');
        }
    }

    public function contentType(): string
    {
        return ContentTypes::NDJSON;
    }

    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        fwrite($this->stream, $payload);

        return new CompletedFuture(null);
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;
        fclose($this->stream);

        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
}
