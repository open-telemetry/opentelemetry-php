<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Adapter\HttpDiscovery;

use Http\Discovery\Psr18ClientDiscovery;
use OpenTelemetry\SDK\Common\Http\Psr\Client\ResolverInterface;
use Psr\Http\Client\ClientInterface;

final class PsrClientResolver implements ResolverInterface
{
    private ?ClientInterface $client;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client;
    }

    public static function create(?ClientInterface $client = null): self
    {
        return new self($client);
    }

    public function resolvePsrClient(): ClientInterface
    {
        return $this->client ??= Psr18ClientDiscovery::find();
    }
}
