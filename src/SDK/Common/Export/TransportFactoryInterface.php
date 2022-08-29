<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

interface TransportFactoryInterface
{
    public function create(
        string $endpoint,
        array $headers = [],
        ?string $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null
    ): TransportInterface;
}
