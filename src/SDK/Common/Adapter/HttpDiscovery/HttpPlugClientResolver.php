<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Adapter\HttpDiscovery;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\HttpClientDiscovery;
use OpenTelemetry\SDK\Common\Http\HttpPlug\Client\ResolverInterface;

final class HttpPlugClientResolver implements ResolverInterface
{
    private ?HttpClient $httpClient;
    private ?HttpAsyncClient $httpAsyncClient;

    public function __construct(?HttpClient $httpClient = null, ?HttpAsyncClient $httpAsyncClient = null)
    {
        $this->httpClient = $httpClient;
        $this->httpAsyncClient = $httpAsyncClient;
    }

    public static function create(?HttpClient $httpClient = null, ?HttpAsyncClient $httpAsyncClient = null): self
    {
        return new self($httpClient, $httpAsyncClient);
    }

    public function resolveHttpPlugClient(): HttpClient
    {
        return $this->httpClient ??= HttpClientDiscovery::find();
    }

    public function resolveHttpPlugAsyncClient(): HttpAsyncClient
    {
        return $this->httpAsyncClient ??= HttpAsyncClientDiscovery::find();
    }
}
