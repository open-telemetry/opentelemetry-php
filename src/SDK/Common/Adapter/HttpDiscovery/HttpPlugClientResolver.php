<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Adapter\HttpDiscovery;

use Http\Client\HttpAsyncClient;
use Http\Discovery\HttpAsyncClientDiscovery;
use OpenTelemetry\SDK\Common\Http\HttpPlug\Client\ResolverInterface;

final class HttpPlugClientResolver implements ResolverInterface
{
    private ?HttpAsyncClient $httpAsyncClient;

    public function __construct(?HttpAsyncClient $httpAsyncClient = null)
    {
        $this->httpAsyncClient = $httpAsyncClient;
    }

    public static function create(?HttpAsyncClient $httpAsyncClient = null): self
    {
        return new self($httpAsyncClient);
    }

    public function resolveHttpPlugAsyncClient(): HttpAsyncClient
    {
        return $this->httpAsyncClient ??= HttpAsyncClientDiscovery::find();
    }
}
