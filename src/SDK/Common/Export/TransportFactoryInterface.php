<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

interface TransportFactoryInterface
{
    public const COMPRESSION_GZIP = 'gzip';
    public const COMPRESSION_DEFLATE = 'deflate';
    public const COMPRESSION_BROTLI = 'br';

    /**
     * @param array<string, string|string[]> $headers
     * @param string|string[]|null $compression
     */
    public function create(
        string $endpoint,
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
