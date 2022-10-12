<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

interface TransportFactoryInterface
{
    public const COMPRESSION_GZIP = 'gzip';
    public const COMPRESSION_DEFLATE = 'deflate';
    public const COMPRESSION_BROTLI = 'br';

    /**
     * @psalm-template CONTENT_TYPE of string
     * @psalm-param CONTENT_TYPE $contentType
     * @psalm-param array<string, string|string[]> $headers
     * @psalm-param string|string[]|null $compression
     * @psalm-return TransportInterface<CONTENT_TYPE>
     */
    public function create(
        string $endpoint,
        string $contentType,
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
