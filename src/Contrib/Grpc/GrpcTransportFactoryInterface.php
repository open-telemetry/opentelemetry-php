<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use OpenTelemetry\SDK\Common\Export\TransportInterface;

interface GrpcTransportFactoryInterface
{
    /**
     * @param array<string, string|string[]> $headers
     * @param string|string[]|null $compression
     */
    public function create(
        string $signal = null,
        string $endpoint = null,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface;
}
