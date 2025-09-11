<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;

class MemoryTransportFactory implements TransportFactoryInterface
{
    public function type(): string
    {
        return 'memory';
    }

    public function priority(): int
    {
        return 0;
    }

    /**
     * @psalm-return TransportInterface<"memory/json">
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function create(string $endpoint, string $contentType, array $headers = [], $compression = null, float $timeout = 10., int $retryDelay = 100, int $maxRetries = 3, ?string $cacert = null, ?string $cert = null, ?string $key = null): TransportInterface
    {
        return new MemoryTransport();
    }
}
